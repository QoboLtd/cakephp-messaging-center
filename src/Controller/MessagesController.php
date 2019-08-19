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
namespace MessagingCenter\Controller;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Event\EventName;
use MessagingCenter\Model\Entity\Folder;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\Table\MailboxesTable;
use Webmozart\Assert\Assert;

/**
 * Messages Controller
 *
 * @property \MessagingCenter\Model\Table\MessagesTable $Messages
 */
class MessagesController extends AppController
{

    /**
     * Folder method
     * @param string $folderName Folder name
     * @return \Cake\Http\Response|void|null
     */
    public function folder(string $folderName = MailboxesTable::FOLDER_INBOX)
    {
        deprecationWarning('Action Messages::folder is no longer supported. Please, use Mailboxes::view instead.');

        /** @var MailboxesTable $mailboxesTable */
        $mailboxesTable = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        $mailbox = $mailboxesTable->createDefaultMailbox($this->Auth->user());
        $folder = $mailboxesTable->getFolderByName($mailbox, $folderName);

        return $this->redirect([
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailbox->get('id'),
            $folder->get('id'),
        ]);
    }

    /**
     * View method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null
     */
    public function view(string $id = null)
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id, [
            'contain' => [
                'Folders' => [
                    'Mailboxes'
                ],
                'FromUser',
                'ToUser',
                'attachments'
            ]
        ]);

        $folder = $this->Messages->getFolderByMessage($message, $this->Auth->user('id'));
        $mailbox = $this->getMailbox($folder->get('mailbox_id'));

        // set status to read
        if ($this->request->is(['get']) && !$this->request->is(['json', 'ajax'])) {
            $message->markAsRead();
            $this->Messages->save($message);
        }

        $attachments = $message->get('attachments');

        $this->set('message', $message);
        $this->set('folderName', $folder->get('name'));
        $this->set('mailbox', $mailbox);
        $this->set('attachments', $attachments);
        $this->set('_serialize', ['message', 'folder', 'mailbox', 'attachments']);
    }

    /**
     * Composer method
     *
     * @param string $mailboxId to compose message for
     * @return \Cake\Http\Response|void|null Redirects on successful compose, renders view otherwise.
     */
    public function compose(string $mailboxId)
    {
        $mailbox = $this->getMailbox($mailboxId);
        $message = $this->Messages->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            Assert::isArray($data);

            $data['from_user'] = $this->Auth->user('id');
            $data['status'] = $this->Messages->getNewStatus();
            $data['date_sent'] = $this->Messages->getDateSent();

            $message = $this->Messages->patchEntity($message, $data);
            if ($this->Messages->save($message)) {
                $this->Flash->success((string)__('The message has been sent.'));

                $this->Messages->processMessages(
                    $this->Auth->user('id'),
                    $mailbox->get('folders')
                );

                $event = new Event((string)EventName::SEND_EMAIL(), $this, [
                    'mailbox' => $mailbox,
                    'data' => $data
                ]);
                EventManager::instance()->dispatch($event);
                $result = $event->result;

                return $this->redirect(['plugin' => 'MessagingCenter', 'controller' => 'Mailboxes', 'action' => 'view', $mailboxId]);
            } else {
                $this->Flash->error((string)__('The message could not be sent. Please, try again.'));
            }
        }

        $this->set('message', $message);
        $this->set('mailbox', $mailbox);
        $this->set('_serialize', ['message', 'mailbox']);
    }

    /**
     * Reply method
     * @param string $id message id
     * @return \Cake\Http\Response|void|null Redirects on successful reply, renders view otherwise.
     */
    public function reply(string $id)
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id, [
            'contain' => ['Folders']
        ]);

        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        $mailbox = $mailboxes->get($message->get('folder')->get('mailbox_id'), [
            'contain' => ['Folders']
        ]);

        // current user's sent message
        if ($mailbox->get('type') === MailboxType::SYSTEM && $this->Auth->user('id') === $message->get('from_user')) {
            $this->Flash->error((string)__('You cannot reply to a sent message.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        if ($this->request->is('put')) {
            $newMessage = $this->Messages->newEntity();
            $data = $this->request->getData();
            Assert::isArray($data);

            $data['to_user'] = $message->get('from_user');
            $data['from_user'] = $this->Auth->user('id');
            $data['status'] = $this->Messages->getNewStatus();
            $data['date_sent'] = $this->Messages->getDateSent();
            $data['related_id'] = $id;
            $newMessage = $this->Messages->patchEntity($newMessage, $data);
            if ($this->Messages->save($newMessage)) {
                $this->Flash->success((string)__('The message has been sent.'));

                return $this->redirect(['plugin' => 'MessagingCenter', 'controller' => 'Mailboxes', 'action' => 'view', $mailbox->get('id')]);
            } else {
                $this->Flash->error((string)__('The message could not be sent. Please, try again.'));
            }
        }

        $this->set('message', $message);
        $this->set('mailbox', $mailbox);
        $this->set('_serialize', ['message', 'mailbox']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null
     */
    public function delete(string $id = null)
    {
        deprecationWarning('Action delete is deprecated. Please, use move instead.');

        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $folder = $this->Messages->getFolderByName($message, MailboxesTable::FOLDER_TRASH);

        return $this->setAction('move', $id, $folder->get('id'));
    }

    /**
     * Archive method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null
     */
    public function archive(string $id = null)
    {
        deprecationWarning('Action archive is deprecated. Please, use move instead.');

        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $folder = $this->Messages->getFolderByName($message, MailboxesTable::FOLDER_ARCHIVE);

        return $this->setAction('move', $id, $folder->get('id'));
    }

    /**
     * Restore method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null
     */
    public function restore(string $id = null)
    {
        deprecationWarning('Action restore is deprecated. Please, use move instead.');

        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $folder = $this->Messages->getFolderByName($message, MailboxesTable::FOLDER_INBOX);

        return $this->setAction('move', $id, $folder->get('id'));
    }

    /**
     * Move method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null
     */
    public function move(string $id, string $folderId)
    {
        $this->request->allowMethod(['post', 'delete']);
        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $foldersTable = TableRegistry::getTableLocator()->get('MessagingCenter.Folders');
        $folder = $foldersTable->get($folderId);
        Assert::isInstanceOf($folder, Folder::class);

        $message->moveToFolder($folder);

        if ($this->Messages->save($message)) {
            $this->Flash->success((string)__('The message has been moved to {0}.', $folder->get('name')));
        } else {
            $this->Flash->error((string)__('The message could not be moved. Please, try again.'));
        }

        return $this->redirect(['controller' => 'mailboxes', 'action' => 'view', $folder->get('mailbox_id'), $folder->get('id')]);
    }

    /**
     * getMailbox method
     *
     * @param string $mailboxId to get mailbox for
     * @return \Cake\Datasource\EntityInterface
     */
    protected function getMailbox(string $mailboxId) : EntityInterface
    {
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);
        $mailbox = $mailboxes->get($mailboxId, [
            'contain' => [
                'Folders' => [
                    'sort' => ['Folders.order_no' => 'ASC']
                ]
            ]
        ]);
        Assert::isInstanceOf($mailbox, Mailbox::class);

        return $mailbox;
    }

    /**
     * getFolderByName method
     *
     * @param string $folderName to find
     * @param string $mailboxId owned folder
     * @return \Cake\Datasource\EntityInterface
     */
    protected function getFolderByName(string $folderName, string $mailboxId) : EntityInterface
    {
        $folders = TableRegistry::getTableLocator()->get('MessagingCenter.Folders');
        $folder = $folders->find()
            ->where([
                'name' => $folderName,
                'mailbox_id' => $mailboxId
            ])
            ->enableHydration(true)
            ->first();

        Assert::isInstanceOf($folder, EntityInterface::class);

        return $folder;
    }
}
