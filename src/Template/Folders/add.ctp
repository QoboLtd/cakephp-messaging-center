<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $folder
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __d('Qobo/MessagingCenter', 'Actions') ?></li>
        <li><?= $this->Html->link(__d('Qobo/MessagingCenter', 'List Folders'), ['action' => 'index']) ?></li>
    </ul>
</nav>
<div class="qoboFolders form large-9 medium-8 columns content">
    <?= $this->Form->create($folder) ?>
    <fieldset>
        <legend><?= __d('Qobo/MessagingCenter', 'Add Folder') ?></legend>
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
