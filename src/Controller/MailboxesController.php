<?php
namespace MessagingCenter\Controller;

use Cake\Core\Configure;
use MessagingCenter\Controller\AppController;

/**
 * Mailboxes Controller
 *
 * @property \MessagingCenter\Model\Table\MailboxesTable $Mailboxes
 *
 * @method \MessagingCenter\Model\Entity\Mailbox[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class MailboxesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
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
     * @return \Cake\Http\Response|void|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id = null)
    {
        $mailbox = $this->Mailboxes->get($id, [
            'contain' => ['Folders']
        ]);

        $this->set('mailbox', $mailbox);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $mailbox = $this->Mailboxes->newEntity();
        if ($this->request->is('post')) {
            /**
             * @var mixed[] $data
             */
            $data = $this->request->getData();
            $mailbox = $this->Mailboxes->patchEntity($mailbox, $data);
            if ($this->Mailboxes->save($mailbox)) {
                $this->Flash->success((string)__('The mailbox has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error((string)__('The mailbox could not be saved. Please, try again.'));
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
     * @return \Cake\Http\Response|void|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id = null)
    {
        $mailbox = $this->Mailboxes->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            /**
             * @var mixed[] $data
             */
            $data = $this->request->getData();
            $mailbox = $this->Mailboxes->patchEntity($mailbox, $data);
            if ($this->Mailboxes->save($mailbox)) {
                $this->Flash->success((string)__('The mailbox has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error((string)__('The mailbox could not be saved. Please, try again.'));
        }
        $this->set(compact('mailbox'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Mailbox id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $mailbox = $this->Mailboxes->get($id);
        if ($this->Mailboxes->delete($mailbox)) {
            $this->Flash->success((string)__('The mailbox has been deleted.'));
        } else {
            $this->Flash->error((string)__('The mailbox could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
