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
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Users']
        ];
        $messages = $this->paginate($this->Messages);

        $this->set(compact('messages'));
        $this->set('_serialize', ['messages']);
    }

    /**
     * View method
     *
     * @param string|null $id Message id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $message = $this->Messages->get($id, [
            'contain' => ['Users']
        ]);

        $this->set('message', $message);
        $this->set('_serialize', ['message']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $message = $this->Messages->newEntity();
        if ($this->request->is('post')) {
            $this->request->data['from_user'] = $this->Auth->user('id');
            $this->request->data['status'] = $this->Messages->getNewMessageStatus();
            $this->request->data['date_sent'] = $this->Messages->getDateSent();
            $message = $this->Messages->patchEntity($message, $this->request->data);
            if ($this->Messages->save($message)) {
                $this->Flash->success(__('The message has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The message could not be saved. Please, try again.'));
            }
        }
        $users = $this->Messages->Users->find('list', ['limit' => 200]);
        $this->set(compact('message', 'users'));
        $this->set('_serialize', ['message']);
    }

    /**
     * Edit method
     *
     * @param string|null $id Message id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $message = $this->Messages->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $message = $this->Messages->patchEntity($message, $this->request->data);
            if ($this->Messages->save($message)) {
                $this->Flash->success(__('The message has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The message could not be saved. Please, try again.'));
            }
        }
        $users = $this->Messages->Users->find('list', ['limit' => 200]);
        $this->set(compact('message', 'users'));
        $this->set('_serialize', ['message']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Message id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $message = $this->Messages->get($id);
        if ($this->Messages->delete($message)) {
            $this->Flash->success(__('The message has been deleted.'));
        } else {
            $this->Flash->error(__('The message could not be deleted. Please, try again.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
