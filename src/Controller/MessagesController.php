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

use Cake\ORM\TableRegistry;
use Cake\Http\Exception\ForbiddenException;
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
     * @param string $folder folder name
     * @return \Cake\Http\Response|void|null
     */
    public function folder(string $folder = '')
    {
        if (!$this->Messages->folderExists($folder)) {
            $folder = $this->Messages->getDefaultFolder();
        }

        $this->paginate = [
            'conditions' => $this->Messages->getConditionsByFolder($this->Auth->user('id'), $folder),
            'contain' => [],
            'order' => ['Messages.date_sent' => 'DESC']
        ];
        $messages = $this->paginate($this->Messages);

        $this->set(compact('messages', 'folder'));
        $this->set('_serialize', ['messages', 'folder']);
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
            'contain' => []
        ]);

        // forbid viewing of others messages
        if (!$this->Auth->user('is_superuser') && $this->Auth->user('id') !== $message->get('to_user') && $this->Auth->user('id') !== $message->get('from_user')) {
            throw new ForbiddenException();
        }

        $folder = $this->Messages->getFolderByMessage($message, $this->Auth->user('id'));
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);
        $mailbox = $mailboxes->get($folder->get('mailbox_id'), [
            'contain' => [
                'Folders' => [
                    'sort' => ['Folders.order_no' => 'ASC']
                ]
            ]
        ]);
        Assert::isInstanceOf($mailbox, Mailbox::class);

        // set status to read
        if ($this->request->is(['get']) &&
            !$this->request->is(['json', 'ajax']) &&
            $this->Messages->getNewStatus() === $message->get('status') &&
            $this->Messages->getSentFolder() !== $folder->get('name')
        ) {
            $status = $this->Messages->getReadStatus();
            $message = $this->Messages->patchEntity($message, ['status' => $status]);
            $this->Messages->save($message);
        }

        $this->set('message', $message);
        $this->set('folderName', $folder->get('name'));
        $this->set('mailbox', $mailbox);
        $this->set('_serialize', ['message', 'folder', 'mailbox']);
    }

    /**
     * Composer method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful compose, renders view otherwise.
     */
    public function compose()
    {
        $message = $this->Messages->newEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['from_user'] = $this->Auth->user('id');
            $data['status'] = $this->Messages->getNewStatus();
            $data['date_sent'] = $this->Messages->getDateSent();
            $message = $this->Messages->patchEntity($message, $data);
            if ($this->Messages->save($message)) {
                $this->Flash->success((string)__('The message has been sent.'));

                return $this->redirect(['action' => 'folder']);
            } else {
                $this->Flash->error((string)__('The message could not be sent. Please, try again.'));
            }
        }

        $this->set('message', $message);
        $this->set('_serialize', ['message']);
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
            'contain' => []
        ]);

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error((string)__('You cannot reply to a sent message.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        if ($this->request->is('put')) {
            $newMessage = $this->Messages->newEntity();
            $data = $this->request->getData();
            $data['to_user'] = $message->from_user;
            $data['from_user'] = $this->Auth->user('id');
            $data['status'] = $this->Messages->getNewStatus();
            $data['date_sent'] = $this->Messages->getDateSent();
            $data['related_id'] = $id;
            $newMessage = $this->Messages->patchEntity($newMessage, $data);
            if ($this->Messages->save($newMessage)) {
                $this->Flash->success((string)__('The message has been sent.'));

                return $this->redirect(['action' => 'folder']);
            } else {
                $this->Flash->error((string)__('The message could not be sent. Please, try again.'));
            }
        }

        $this->set('message', $message);
        $this->set('_serialize', ['message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null Redirects to folder.
     */
    public function delete(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $status = $this->Messages->getDeletedStatus();

        // already deleted message
        if ($message->status === $status) {
            $this->Flash->error((string)__('You cannot delete a deleted message.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error((string)__('You cannot delete a sent message.'));

            return $this->redirect(['action' => 'view', $id]);
        }

        $message = $this->Messages->patchEntity($message, ['status' => $status]);

        if ($this->Messages->save($message)) {
            $this->Flash->success((string)__('The message has been deleted.'));
        } else {
            $this->Flash->error((string)__('The message could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'folder']);
    }

    /**
     * Archive method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null Redirects to folder.
     */
    public function archive(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $status = $this->Messages->getArchivedStatus();

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error((string)__('You cannot archive a sent message.'));

            return $this->redirect(['action' => 'view', $id]);
        } else {
            // already archived message
            if ($message->status === $status) {
                $this->Flash->error((string)__('You cannot arcive an archived message.'));

                return $this->redirect(['action' => 'view', $id]);
            }
        }

        $message = $this->Messages->patchEntity($message, ['status' => $status]);

        if ($this->Messages->save($message)) {
            $this->Flash->success((string)__('The message has been archived.'));
        } else {
            $this->Flash->error((string)__('The message could not be archived. Please, try again.'));
        }

        return $this->redirect(['action' => 'folder']);
    }

    /**
     * Restore method
     *
     * @param string|null $id Message id.
     * @return \Cake\Http\Response|void|null Redirects to folder.
     */
    public function restore(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        /**
         * @var \MessagingCenter\Model\Entity\Message $message
         */
        $message = $this->Messages->get($id);

        $status = $this->Messages->getReadStatus();

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error((string)__('You cannot restore a sent message.'));

            return $this->redirect(['action' => 'view', $id]);
        } else {
            // inbox message
            if (in_array($message->status, [$status, $this->Messages->getNewStatus()])) {
                $this->Flash->error((string)__('You cannot restore an inbox message.'));

                return $this->redirect(['action' => 'view', $id]);
            }
        }

        $message = $this->Messages->patchEntity($message, ['status' => $status]);

        if ($this->Messages->save($message)) {
            $this->Flash->success((string)__('The message has been restored.'));
        } else {
            $this->Flash->error((string)__('The message could not be restored. Please, try again.'));
        }

        return $this->redirect(['action' => 'folder']);
    }
}
