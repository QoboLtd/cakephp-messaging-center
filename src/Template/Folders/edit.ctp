<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $qoboFolder
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $qoboFolder->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $qoboFolder->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Qobo Folders'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="qoboFolders form large-9 medium-8 columns content">
    <?= $this->Form->create($qoboFolder) ?>
    <fieldset>
        <legend><?= __('Edit Qobo Folder') ?></legend>
        <?php
            echo $this->Form->control('mailbox_id');
            echo $this->Form->control('parent_id');
            echo $this->Form->control('name');
            echo $this->Form->control('type');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
