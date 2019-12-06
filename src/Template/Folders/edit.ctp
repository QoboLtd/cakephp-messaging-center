<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $qoboFolder
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __d('Qobo/MessagingCenter', 'Actions') ?></li>
        <li><?= $this->Form->postLink(
                __d('Qobo/MessagingCenter', 'Delete'),
                ['action' => 'delete', $folder->get('id')],
                ['confirm' => __d('Qobo/MessagingCenter', 'Are you sure you want to delete # {0}?', $folder->get('id'))]
            )
        ?></li>
        <li><?= $this->Html->link(__d('Qobo/MessagingCenter', 'List Qobo Folders'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="qoboFolders form large-9 medium-8 columns content">
    <?= $this->Form->create($folder) ?>
    <fieldset>
        <legend><?= __d('Qobo/MessagingCenter', 'Edit Qobo Folder') ?></legend>
        <?php
            echo $this->Form->control('mailbox_id');
            echo $this->Form->control('parent_id');
            echo $this->Form->control('name');
            echo $this->Form->control('type');
        ?>
    </fieldset>
    <?= $this->Form->button(__d('Qobo/MessagingCenter', 'Submit')) ?>
    <?= $this->Form->end() ?>
</div>
