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
     * @param string $type folder type
     * @return void
     */
    public function folder($type = '')
    {
        $this->paginate = [
            'conditions' => $this->Messages->getConditionsByFolderType($this->Auth->user('id'), $type),
            'contain' => ['FromUser', 'ToUser'],
            'order' => ['Messages.date_sent' => 'DESC']
        ];
        $messages = $this->paginate($this->Messages);

        $this->set(compact('messages'));
        $this->set('_serialize', ['messages']);
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
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function create()
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
     *
     * @return \Cake\Network\Response|void Redirects on successful reply, renders view otherwise.
     */
    public function reply($id)
    {
        $message = $this->Messages->get($id, [
            'contain' => ['FromUser', 'ToUser']
        ]);
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

        if ($this->Auth->user('id') !== $message->to_user || $message->status === $status) {
            throw new \Cake\Network\Exception\ForbiddenException();
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

        if ($this->Auth->user('id') === $message->to_user && $this->Messages->save($message)) {
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

        if ($this->Auth->user('id') === $message->to_user && $this->Messages->save($message)) {
            $this->Flash->success(__('The message has been restored.'));
        } else {
            $this->Flash->error(__('The message could not be restored. Please, try again.'));
        }
        return $this->redirect(['action' => 'folder']);
    }
}
