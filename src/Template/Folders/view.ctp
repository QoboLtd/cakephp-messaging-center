<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $qoboFolder
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Qobo Folder'), ['action' => 'edit', $qoboFolder->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Qobo Folder'), ['action' => 'delete', $qoboFolder->id], ['confirm' => __('Are you sure you want to delete # {0}?', $qoboFolder->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Qobo Folders'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Qobo Folder'), ['action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="qoboFolders view large-9 medium-8 columns content">
    <h3><?= h($qoboFolder->name) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= h($qoboFolder->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Mailbox Id') ?></th>
            <td><?= h($qoboFolder->mailbox_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Parent Id') ?></th>
            <td><?= h($qoboFolder->parent_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Name') ?></th>
            <td><?= h($qoboFolder->name) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Type') ?></th>
            <td><?= h($qoboFolder->type) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($qoboFolder->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($qoboFolder->modified) ?></td>
        </tr>
    </table>
</div>
