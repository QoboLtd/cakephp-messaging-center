<?php
namespace MessagingCenter\Controller;

/**
 * Folders Controller
 *
 * @property \MessagingCenter\Model\Table\FoldersTable $Folders
 *
 * @method \MessagingCenter\Model\Entity\Folder[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FoldersController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void|null
     */
    public function index()
    {
        $folders = $this->paginate($this->Folders);

        $this->set(compact('folders'));
    }

    /**
     * View method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Http\Response|void|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(string $id = null)
    {
        $folder = $this->Folders->get($id, [
            'contain' => ['Messages'],
        ]);

        $this->set('folder', $folder);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|void|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $folder = $this->Folders->newEntity();
        if ($this->request->is('post')) {
            /**
             * @var mixed[] $data
             */
            $data = $this->request->getData();
            $this->Folders->patchEntity($folder, $data);

            if ($this->Folders->save($folder)) {
                $this->Flash->success((string)__d('Qobo/MessagingCenter', 'The folder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error((string)__d('Qobo/MessagingCenter', 'The folder could not be saved. Please, try again.'));
        }
        $this->set(compact('folder'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Http\Response|void|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(string $id = null)
    {
        $folder = $this->Folders->get($id, [
            'contain' => [],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            /**
             * @var mixed[] $data
             */
            $data = $this->request->getData();
            $folder = $this->Folders->patchEntity($folder, $data);
            if ($this->Folders->save($folder)) {
                $this->Flash->success((string)__d('Qobo/MessagingCenter', 'The folder has been saved.'));

                return $this->redirect(['action' => 'view', $id]);
            }
            $this->Flash->error((string)__d('Qobo/MessagingCenter', 'The folder could not be saved. Please, try again.'));
        }

        $this->set(compact('folder'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Http\Response|void|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $folder = $this->Folders->get($id);

        if ($this->Folders->delete($folder)) {
            $this->Flash->success((string)__d('Qobo/MessagingCenter', 'The folder has been deleted.'));
        } else {
            $this->Flash->error((string)__d('Qobo/MessagingCenter', 'The folder could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
