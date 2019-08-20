<?php
namespace MessagingCenter\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use MessagingCenter\Model\Table\FoldersTable;
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Model\Table\MessagesTable;
use Webmozart\Assert\Assert;

/**
 * Mailboxes Controller
 *
 * @property \MessagingCenter\Model\Table\FoldersTable $Folders
 * @property \MessagingCenter\Model\Table\MailboxesTable $Mailboxes
 * @property \MessagingCenter\Model\Table\MessagesTable $Messages
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
     * @param string|null $folderId Folder id
     * @return \Cake\Http\Response|void|null
     */
    public function view(string $id = null, string $folderId = null)
    {
        $mailbox = $this->Mailboxes->get($id, [
            'contain' => [
                'Folders' => [
                    'sort' => ['Folders.order_no' => 'ASC']
                ]
            ]
        ]);

        $folderId = $folderId ?? $this->request->getQuery('folder_id');
        if (empty($folderId)) {
            $folder = $this->Mailboxes->getFolderByName($mailbox, MailboxesTable::FOLDER_INBOX);
        } else {
            $this->loadModel('MessagingCenter.Folders');
            Assert::isInstanceOf($this->Folders, FoldersTable::class);

            $folder = $this->Folders->get($folderId);
        }

        $folderName = $folder->get('name');

        $this->loadModel('MessagingCenter.Messages');
        Assert::isInstanceOf($this->Messages, MessagesTable::class);

        $this->paginate = [
            'conditions' => [
                'folder_id' => $folder->get('id')
            ],
            'contain' => [
                'FromUser',
                'ToUser',
            ],
            'order' => ['Messages.created' => 'DESC']
        ];
        $messages = $this->paginate($this->Messages);

        $this->set(compact('messages', 'folderName', 'mailbox'));
        $this->set('_serialize', ['messages', 'folderName', 'mailbox']);
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

            $data['incoming_settings'] = json_encode($data['IncomingSettings']);
            $data['outgoing_settings'] = json_encode($data['OutgoingSettings']);

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

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find('list')
            ->where([
                'active' => true
            ]);
        $this->set(compact('mailbox', 'types', 'incomingTransports', 'outgoingTransports', 'users'));
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
            'contain' => [
                'Folders' => [
                    'sort' => ['Folders.order_no' => 'ASC']
                ]
            ]
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            /**
             * @var mixed[] $data
             */
            $data = $this->request->getData();

            $data['incoming_settings'] = json_encode($data['IncomingSettings']);
            $data['outgoing_settings'] = json_encode($data['OutgoingSettings']);

            $mailbox = $this->Mailboxes->patchEntity($mailbox, $data);
            if ($this->Mailboxes->save($mailbox)) {
                $this->Flash->success((string)__('The mailbox has been saved.'));

                return $this->redirect(['action' => 'view', $id]);
            }
            $this->Flash->error((string)__('The mailbox could not be saved. Please, try again.'));
        }

        $types = (array)Configure::read('MessagingCenter.Mailbox.types');
        $incomingTransports = (array)Configure::read('MessagingCenter.Mailbox.incomingTransports');
        $outgoingTransports = (array)Configure::read('MessagingCenter.Mailbox.outgoingTransports');

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find('list')
            ->where([
                'active' => true
            ]);
        $this->set(compact('mailbox', 'types', 'incomingTransports', 'outgoingTransports', 'users'));
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
        $this->autoRender = false;

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
