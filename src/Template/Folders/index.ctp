<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $qoboFolders
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Qobo Folder'), ['action' => 'add']) ?></li>
    </ul>
</nav>
<div class="qoboFolders index large-9 medium-8 columns content">
    <h3><?= __('Qobo Folders') ?></h3>
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
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($qoboFolders as $qoboFolder): ?>
            <tr>
                <td><?= h($qoboFolder->id) ?></td>
                <td><?= h($qoboFolder->mailbox_id) ?></td>
                <td><?= h($qoboFolder->parent_id) ?></td>
                <td><?= h($qoboFolder->name) ?></td>
                <td><?= h($qoboFolder->type) ?></td>
                <td><?= h($qoboFolder->created) ?></td>
                <td><?= h($qoboFolder->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $qoboFolder->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $qoboFolder->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $qoboFolder->id], ['confirm' => __('Are you sure you want to delete # {0}?', $qoboFolder->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
