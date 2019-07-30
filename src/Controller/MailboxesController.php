<?php
namespace MessagingCenter\Controller;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use MessagingCenter\Controller\AppController;
use MessagingCenter\Model\Table\FoldersTable;
use MessagingCenter\Model\Table\MessagesTable;
use MessagingCenter\Shell\FetchMailShell;
use PhpImap\Mailbox as RemoteMailbox;
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
     * @return \Cake\Http\Response|void|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id = null)
    {
        $mailbox = $this->Mailboxes->get($id, [
            'contain' => [
                'Folders' => [
                    'sort' => ['Folders.order_no' => 'ASC']
                ]
            ]
        ]);

        $folderId = $this->request->getQuery('folder_id');
        if (empty($folderId)) {
            $folderId = $this->Mailboxes->getInboxFolder($mailbox);
        }

        $this->loadModel('MessagingCenter.Folders');
        Assert::isInstanceOf($this->Folders, FoldersTable::class);

        $folder = $this->Folders->get($folderId);
        $folderName = $folder->get('name');

        $this->loadModel('MessagingCenter.Messages');
        Assert::isInstanceOf($this->Messages, MessagesTable::class);

        $this->paginate = [
            'conditions' => [
                'folder_id' => $folderId
            ],
            'contain' => [],
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
            $data['default_folder'] = json_encode($data['default_folder']);

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
        $allowedMailFolders = (array)Configure::read('MessagingCenter.allowedMailFolders', []);

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find('list')
            ->where([
                'active' => true
            ]);

        $defaultSettings = [
            'username' => '',
            'password' => '',
            'host' => 'localhost',
            'port' => null,
            'protocol' => 'imap',
            'mail_folder' => ''
        ];

        $settings = json_decode($mailbox->get('incoming_settings'), true) ?? [];
        $settings = array_merge($defaultSettings, $settings);

        $fetchMailShell = new FetchMailShell();
        $connectionString = $fetchMailShell->getConnectionString($mailbox->get('incoming_transport'), $settings);

        $remoteMailbox = new RemoteMailbox($connectionString, $settings['username'], $settings['password']);
        $folders = $remoteMailbox->getMailboxes('*');
        $selected_folder_list = json_decode($mailbox->get('default_folder'), true) ?? [];

        $folder_list = [];
        foreach ($folders as $mailbox_folder) {
            if (in_array($mailbox_folder['shortpath'], $allowedMailFolders)) {
                $folder_list[$mailbox_folder['shortpath']] = $mailbox_folder['shortpath'];
            }
        }

        $this->set(compact('mailbox', 'types', 'incomingTransports', 'outgoingTransports', 'users', 'folder_list', 'selected_folder_list'));
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
