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
use Cake\ORM\TableRegistry;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Entity\Folder;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\Entity\Message;
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
                    'Mailboxes',
                ],
                'FromUser',
                'ToUser',
                'attachments',
            ],
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
        /**
         * @var \MessagingCenter\Model\Entity\Mailbox $mailbox
         */
        $mailbox = $this->getMailbox($mailboxId);

        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $this->createMessage($mailbox);

        $message = $this->Messages->newEntity();

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
            'contain' => ['Folders', 'ToUser', 'FromUser'],
        ]);

        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');

        /**
         * @var \MessagingCenter\Model\Entity\Mailbox $mailbox
         */
        $mailbox = $mailboxes->get($message->get('folder')->get('mailbox_id'), [
            'contain' => ['Folders'],
        ]);

        $this->createMessage($mailbox, $message);

        $this->set('message', $message);
        $this->set('mailbox', $mailbox);
        $this->set('_serialize', ['message', 'mailbox']);
    }

    /**
     * createMessage description
     * @param  Mailbox $mailbox         Current Mailbox
     * @param  Message|null $originalMessage Original Message for reply
     * @return \Cake\Http\Response|void|null Redirects on successful reply, renders view otherwise.
     */
    public function createMessage(Mailbox $mailbox, ?Message $originalMessage = null)
    {
        if (MailboxType::SYSTEM !== $mailbox->get('type')) {
            $this->Flash->error(
                sprintf((string)__d('Qobo/MessagingCenter', 'Composing messages for "%s" mailbox is not supported.'), $mailbox->get('type'))
            );

            return $this->redirect($this->referer());
        }

        // current user's sent message
        if ($originalMessage && $mailbox->get('type') === MailboxType::SYSTEM && $this->Auth->user('id') === $originalMessage->get('from_user')) {
            $this->Flash->error((string)__d('Qobo/MessagingCenter', 'You cannot reply to a sent message.'));

            return $this->redirect(['action' => 'view', $originalMessage->get('id')]);
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            Assert::isArray($data);

            $userId = $this->Auth->user('id');

            $result = $this->Messages->createMessage($mailbox, $originalMessage, $data, $userId);

            if ($result) {
                $this->Flash->success((string)__d('Qobo/MessagingCenter', 'The message has been sent.'));

                $this->redirect(['plugin' => 'MessagingCenter', 'controller' => 'Mailboxes', 'action' => 'view', $mailbox->get('id')]);
            } else {
                $this->Flash->error((string)__d('Qobo/MessagingCenter', 'The message could not be sent. Please, try again.'));
            }
        }
    }

    /**
     * Move method
     *
     * @param string $id Message id
     * @param string $folderId Folder id
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

        $mailbox = $this->loadModel('MessagingCenter.Mailboxes')->get($folder->get('mailbox_id'));
        if (MailboxType::SYSTEM !== $mailbox->get('type')) {
            $this->Flash->error(
                sprintf((string)__d('Qobo/MessagingCenter', 'Moving messages for "%s" mailbox is not supported.'), $mailbox->get('type'))
            );

            return $this->redirect($this->referer());
        }

        $message->moveToFolder($folder);

        if ($this->Messages->save($message)) {
            $this->Flash->success((string)__d('Qobo/MessagingCenter', 'The message has been moved to {0}.', $folder->get('name')));

            return $this->redirect(['controller' => 'mailboxes', 'action' => 'view', $folder->get('mailbox_id'), $folder->get('id')]);
        } else {
            $this->Flash->error((string)__d('Qobo/MessagingCenter', 'The message could not be moved. Please, try again.'));
        }
    }

    /**
     * getMailbox method
     *
     * @param string $mailboxId to get mailbox for
     * @return \Cake\Datasource\EntityInterface
     */
    protected function getMailbox(string $mailboxId): EntityInterface
    {
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);
        $mailbox = $mailboxes->get($mailboxId, [
            'contain' => [
                'Folders' => [
                    'sort' => ['Folders.order_no' => 'ASC'],
                ],
            ],
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
    protected function getFolderByName(string $folderName, string $mailboxId): EntityInterface
    {
        $folders = TableRegistry::getTableLocator()->get('MessagingCenter.Folders');
        $folder = $folders->find()
            ->where([
                'name' => $folderName,
                'mailbox_id' => $mailboxId,
            ])
            ->enableHydration(true)
            ->first();

        Assert::isInstanceOf($folder, EntityInterface::class);

        return $folder;
    }
}
