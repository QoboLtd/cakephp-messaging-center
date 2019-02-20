<?php
namespace MessagingCenter\Controller;

use Cake\Core\Configure;
use MessagingCenter\Controller\AppController;

/**
 * Mailboxes Controller
 *
 *
 * @method \MessagingCenter\Model\Entity\Mailbox[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MailboxesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $mailboxes = $this->paginate($this->Mailboxes);

        $this->set(compact('mailboxes'));
    }

    /**
     * View method
     *
     * @param string|null $id Mailbox id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $mailbox = $this->Mailboxes->get($id, [
            'contain' => ['Folders']
        ]);

        $this->set('mailbox', $mailbox);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mailbox = $this->Mailboxes->newEntity();
        if ($this->request->is('post')) {
            $mailbox = $this->Mailboxes->patchEntity($mailbox, $this->request->getData());
            if ($this->Mailboxes->save($mailbox)) {
                $this->Flash->success(__('The mailbox has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mailbox could not be saved. Please, try again.'));
        }

        $types = (array)Configure::read('MessagingCenter.Mailbox.types');
        $incomingTransports = (array)Configure::read('MessagingCenter.Mailbox.incomingTransports');
        $outgoingTransports = (array)Configure::read('MessagingCenter.Mailbox.outgoingTransports');

        $this->set(compact('mailbox', 'types', 'incomingTransports', 'outgoingTransports'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Mailbox id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $mailbox = $this->Mailboxes->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $mailbox = $this->Mailboxes->patchEntity($mailbox, $this->request->getData());
            if ($this->Mailboxes->save($mailbox)) {
                $this->Flash->success(__('The mailbox has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The mailbox could not be saved. Please, try again.'));
        }
        $this->set(compact('mailbox'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Mailbox id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mailbox = $this->Mailboxes->get($id);
        if ($this->Mailboxes->delete($mailbox)) {
            $this->Flash->success(__('The mailbox has been deleted.'));
        } else {
            $this->Flash->error(__('The mailbox could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
