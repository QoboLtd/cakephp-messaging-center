<?php
namespace MessagingCenter\Controller;

use MessagingCenter\Controller\AppController;

/**
 * Folders Controller
 *
 *
 * @method \MessagingCenter\Model\Entity\Folder[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class FoldersController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
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
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $folder = $this->Folders->get($id, [
            'contain' => []
        ]);

        $this->set('folder', $folder);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $folder = $this->Folders->newEntity();
        if ($this->request->is('post')) {
            $qoboFolder = $this->Folders->patchEntity($folder, $this->request->getData());
            if ($this->Folders->save($folder)) {
                $this->Flash->success(__('The folder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The folder could not be saved. Please, try again.'));
        }
        $this->set(compact('folder'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $folder = $this->Folders->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $folder = $this->Folders->patchEntity($folder, $this->request->getData());
            if ($this->Folders->save($folder)) {
                $this->Flash->success(__('The folder has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The folder could not be saved. Please, try again.'));
        }
        $this->set(compact('folder'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Folder id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $folder = $this->Folders->get($id);
        if ($this->Folders->delete($folder)) {
            $this->Flash->success(__('The folder has been deleted.'));
        } else {
            $this->Flash->error(__('The folder could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
