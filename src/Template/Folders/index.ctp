<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $folders
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __d('Qobo/MessagingCenter', 'Actions') ?></li>
        <li><?= $this->Html->link(__d('Qobo/MessagingCenter', 'New Folder'), ['action' => 'add']) ?></li>
    </ul>
</nav>
<div class="folders index large-9 medium-8 columns content">
    <h3><?= __d('Qobo/MessagingCenter', 'Folders') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('mailbox_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('parent_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                <th scope="col"><?= $this->Paginator->sort('type') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __d('Qobo/MessagingCenter', 'Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($folders as $folder): ?>
            <tr>
                <td><?= h($folder->id) ?></td>
                <td><?= h($folder->mailbox_id) ?></td>
                <td><?= h($folder->parent_id) ?></td>
                <td><?= h($folder->name) ?></td>
                <td><?= h($folder->type) ?></td>
                <td><?= h($folder->created) ?></td>
                <td><?= h($folder->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__d('Qobo/MessagingCenter', 'View'), ['action' => 'view', $folder->id]) ?>
                    <?= $this->Html->link(__d('Qobo/MessagingCenter', 'Edit'), ['action' => 'edit', $folder->id]) ?>
                    <?= $this->Form->postLink(__d('Qobo/MessagingCenter', 'Delete'), ['action' => 'delete', $folder->id], ['confirm' => __('Are you sure you want to delete # {0}?', $folder->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __d('Qobo/MessagingCenter', 'first')) ?>
            <?= $this->Paginator->prev('< ' . __d('Qobo/MessagingCenter', 'previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__d('Qobo/MessagingCenter', 'next') . ' >') ?>
            <?= $this->Paginator->last(__d('Qobo/MessagingCenter', 'last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __d('Qobo/MessagingCenter', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
