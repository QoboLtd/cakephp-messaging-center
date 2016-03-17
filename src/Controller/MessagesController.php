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
            'conditions' => $this->Messages->getConditionsByFolderType($type, $this->Auth->user('id')),
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

        // set status to read
        if ($this->request->is(['get']) &&
            !$this->request->is(['json', 'ajax']) &&
            $this->Messages->getNewStatus() === $message->status
        ) {
            $status = $this->Messages->getReadStatus();
            $message = $this->Messages->patchEntity($message, ['status' => $status]);
            $this->Messages->save($message);
        }

        $folder = $this->Messages->getFolderByMessage($message, $this->Auth->user('id'));

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
}
