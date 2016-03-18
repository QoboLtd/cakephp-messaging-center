<?php
namespace MessagingCenter\Controller;

use MessagingCenter\Controller\AppController;

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
     * @return void
     */
    public function folder($folder = '')
    {
        $folder = $this->Messages->getFolder($folder);

        $this->paginate = [
            'conditions' => $this->Messages->getConditionsByFolder($this->Auth->user('id'), $folder),
            'contain' => ['FromUser', 'ToUser'],
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
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $message = $this->Messages->get($id, [
            'contain' => ['FromUser', 'ToUser']
        ]);

        // forbid viewing of others messages
        if ($this->Auth->user('id') !== $message->to_user && $this->Auth->user('id') !== $message->from_user) {
            throw new \Cake\Network\Exception\ForbiddenException();
        }

        $folder = $this->Messages->getFolderByMessage($message, $this->Auth->user('id'));

        // set status to read
        if ($this->request->is(['get']) &&
            !$this->request->is(['json', 'ajax']) &&
            $this->Messages->getNewStatus() === $message->status &&
            $this->Messages->getSentFolder() !== $folder
        ) {
            $status = $this->Messages->getReadStatus();
            $message = $this->Messages->patchEntity($message, ['status' => $status]);
            $this->Messages->save($message);
        }

        $this->set('message', $message);
        $this->set('folder', $folder);
        $this->set('_serialize', ['message', 'folder']);
    }

    /**
     * Composer method
     *
     * @return \Cake\Network\Response|void Redirects on successful compose, renders view otherwise.
     */
    public function compose()
    {
        $message = $this->Messages->newEntity();
        if ($this->request->is('post')) {
            $this->request->data['from_user'] = $this->Auth->user('id');
            $this->request->data['status'] = $this->Messages->getNewStatus();
            $this->request->data['date_sent'] = $this->Messages->getDateSent();
            $message = $this->Messages->patchEntity($message, $this->request->data);
            if ($this->Messages->save($message)) {
                $this->Flash->success(__('The message has been saved.'));
                return $this->redirect(['action' => 'folder']);
            } else {
                $this->Flash->error(__('The message could not be saved. Please, try again.'));
            }
        }
        $users = $this->Messages->ToUser->find('list', ['limit' => 200]);
        $this->set(compact('message', 'users'));
        $this->set('_serialize', ['message']);
    }

    /**
     * Reply method
     * @param string $id message id
     * @return \Cake\Network\Response|void Redirects on successful reply, renders view otherwise.
     */
    public function reply($id)
    {
        $message = $this->Messages->get($id, [
            'contain' => ['FromUser', 'ToUser']
        ]);

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error(__('You cannot reply to a sent message.'));
            return $this->redirect(['action' => 'view', $id]);
        }

        if ($this->request->is('put')) {
            $newMessage = $this->Messages->newEntity();
            $this->request->data['from_user'] = $this->Auth->user('id');
            $this->request->data['status'] = $this->Messages->getNewStatus();
            $this->request->data['date_sent'] = $this->Messages->getDateSent();
            $this->request->data['related_id'] = $id;
            $newMessage = $this->Messages->patchEntity($newMessage, $this->request->data);
            if ($this->Messages->save($newMessage)) {
                $this->Flash->success(__('The message has been sent.'));
                return $this->redirect(['action' => 'folder']);
            } else {
                $this->Flash->error(__('The message could not be sent. Please, try again.'));
            }
        }
        $users = $this->Messages->ToUser->find('list', ['limit' => 200]);
        $this->set(compact('message', 'users'));
        $this->set('_serialize', ['message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Message id.
     * @return \Cake\Network\Response|null Redirects to folder.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id);

        $status = $this->Messages->getDeletedStatus();

        // already deleted message
        if ($message->status === $status) {
            $this->Flash->error(__('You cannot delete a deleted message.'));
            return $this->redirect(['action' => 'view', $id]);
        }

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error(__('You cannot delete a sent message.'));
            return $this->redirect(['action' => 'view', $id]);
        }

        $message = $this->Messages->patchEntity($message, ['status' => $status]);

        if ($this->Messages->save($message)) {
            $this->Flash->success(__('The message has been deleted.'));
        } else {
            $this->Flash->error(__('The message could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'folder']);
    }

    /**
     * Archive method
     *
     * @param string|null $id Message id.
     * @return \Cake\Network\Response|null Redirects to folder.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function archive($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id);

        $status = $this->Messages->getArchivedStatus();
        $message = $this->Messages->patchEntity($message, ['status' => $status]);

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error(__('You cannot archive a sent message.'));
            return $this->redirect(['action' => 'view', $id]);
        } else {
            // already archived message
            if ($message->status === $status) {
                $this->Flash->error(__('You cannot arcive an archived message.'));
                return $this->redirect(['action' => 'view', $id]);
            }
        }

        if ($this->Messages->save($message)) {
            $this->Flash->success(__('The message has been archived.'));
        } else {
            $this->Flash->error(__('The message could not be archived. Please, try again.'));
        }
        return $this->redirect(['action' => 'folder']);
    }

    /**
     * Restore method
     *
     * @param string|null $id Message id.
     * @return \Cake\Network\Response|null Redirects to folder.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function restore($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id);

        $status = $this->Messages->getReadStatus();
        $message = $this->Messages->patchEntity($message, ['status' => $status]);

        // current user's sent message
        if ($this->Auth->user('id') !== $message->to_user) {
            $this->Flash->error(__('You cannot restore a sent message.'));
            return $this->redirect(['action' => 'view', $id]);
        } else {
            // inbox message
            if (in_array($message->status, [$status, $this->Messages->getNewStatus()])) {
                $this->Flash->error(__('You cannot restore an inbox message.'));
                return $this->redirect(['action' => 'view', $id]);
            }
        }

        if ($this->Messages->save($message)) {
            $this->Flash->success(__('The message has been restored.'));
        } else {
            $this->Flash->error(__('The message could not be restored. Please, try again.'));
        }
        return $this->redirect(['action' => 'folder']);
    }
}
