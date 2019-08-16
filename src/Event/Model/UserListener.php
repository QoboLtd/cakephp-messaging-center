<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MessagingCenter\Event\Model;

use ArrayObject;
use CakeDC\Users\Controller\Traits\CustomUsersTableTrait;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use MessagingCenter\Event\EventName;
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Notifier\MessageNotifier;

class UserListener implements EventListenerInterface
{
    use CustomUsersTableTrait;
    use EventDispatcherTrait;

    /**
     * Notifier instance.
     *
     * @var \MessagingCenter\Notifier\Notifier
     */
    protected $Notifier = null;

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
             (string)EventName::CAKE_ORM_MODEL_AFTER_SAFE() => [
                'callable' => 'afterSave',
                'priority' => 12,
             ]
        ];
    }

    /**
     * After save event.
     *
     * @param \Cake\Event\Event $event event.
     * @param \Cake\Datasource\EntityInterface $entity entity.
     * @param \ArrayObject $options options.
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        /**
         * @var \Cake\ORM\Table $subject
         */
        $subject = $event->getSubject();
        if ($subject->getTable() !== $this->getUsersTable()->getTable()) {
            return;
        }

        if (!$entity->isNew()) {
            return;
        }

        /** @var \MessagingCenter\Model\Table\MailboxesTable $mailboxes  */
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        $defaultMailbox = $mailboxes->createDefaultMailbox($entity->toArray());

        if (!Configure::read('MessagingCenter.welcomeMessage.enabled')) {
            return;
        }

        $this->Notifier = new MessageNotifier();
        $this->Notifier->from(Configure::readOrFail('MessagingCenter.systemUser.id'));
        $this->Notifier->to($entity->id);

        $projectName = Configure::read('MessagingCenter.welcomeMessage.projectName');

        $subject = 'Welcome';
        if (!empty($projectName)) {
            $subject .= ' to ' . $projectName;
        }

        // TODO: find a better way
        $username = empty($entity->username) ? '' : $entity->username;
        $data = [
            'username' => $username,
            'projectName' => $projectName,
            'subject' => $subject,
            'adminName' => Configure::readOrFail('MessagingCenter.systemUser.name'),
            'folder' => $mailboxes->getFolderByName($defaultMailbox, MailboxesTable::FOLDER_INBOX),
        ];
        $this->Notifier->template('MessagingCenter.welcome');

        // broadcast event for modifying message data before passing them to the Notifier
        $event = new Event((string)EventName::NOTIFY_BEFORE_RENDER(), $this, [
            'table' => TableRegistry::get('Messages'),
            'entity' => $entity,
            'data' => $data
        ]);
        $this->getEventManager()->dispatch($event);
        $data = !empty($event->result) ? $event->result : $data;

        $this->Notifier->subject($data['subject']);
        $this->Notifier->message($data);
        $this->Notifier->folder($data['folder']);

        $this->Notifier->send();
    }
}
