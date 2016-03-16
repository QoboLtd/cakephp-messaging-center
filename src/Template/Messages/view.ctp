<?php
$this->extend('../Layout/TwitterBootstrap/dashboard');


$this->start('tb_actions');
?>
<li><?= $this->Html->link(__('Edit Message'), ['action' => 'edit', $message->id]) ?> </li>
<li><?= $this->Form->postLink(__('Delete Message'), ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id)]) ?> </li>
<li><?= $this->Html->link(__('List Messages'), ['action' => 'index']) ?> </li>
<li><?= $this->Html->link(__('New Message'), ['action' => 'add']) ?> </li>
<li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
<li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
<?php
$this->end();

$this->start('tb_sidebar');
?>
<ul class="nav nav-sidebar">
<li><?= $this->Html->link(__('Edit Message'), ['action' => 'edit', $message->id]) ?> </li>
<li><?= $this->Form->postLink(__('Delete Message'), ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id)]) ?> </li>
<li><?= $this->Html->link(__('List Messages'), ['action' => 'index']) ?> </li>
<li><?= $this->Html->link(__('New Message'), ['action' => 'add']) ?> </li>
<li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
<li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
</ul>
<?php
$this->end();
?>
<div class="panel panel-default">
    <!-- Panel header -->
    <div class="panel-heading">
        <h3 class="panel-title"><?= h($message->id) ?></h3>
    </div>
    <table class="table table-striped" cellpadding="0" cellspacing="0">
        <tr>
            <td><?= __('Id') ?></td>
            <td><?= h($message->id) ?></td>
        </tr>
        <tr>
            <td><?= __('User') ?></td>
            <td><?= $message->has('user') ? $this->Html->link($message->user->username, ['controller' => 'Users', 'action' => 'view', $message->user->id]) : '' ?></td>
        </tr>
        <tr>
            <td><?= __('To') ?></td>
            <td><?= h($message->to) ?></td>
        </tr>
        <tr>
            <td><?= __('Subject') ?></td>
            <td><?= h($message->subject) ?></td>
        </tr>
        <tr>
            <td><?= __('Status') ?></td>
            <td><?= h($message->status) ?></td>
        </tr>
        <tr>
            <td><?= __('Related Model') ?></td>
            <td><?= h($message->related_model) ?></td>
        </tr>
        <tr>
            <td><?= __('Related Id') ?></td>
            <td><?= h($message->related_id) ?></td>
        </tr>
        <tr>
            <td><?= __('Date Sent') ?></td>
            <td><?= h($message->date_sent) ?></td>
        </tr>
        <tr>
            <td><?= __('Created') ?></td>
            <td><?= h($message->created) ?></td>
        </tr>
        <tr>
            <td><?= __('Modified') ?></td>
            <td><?= h($message->modified) ?></td>
        </tr>
        <tr>
            <td><?= __('Content') ?></td>
            <td><?= $this->Text->autoParagraph(h($message->content)); ?></td>
        </tr>
    </table>
</div>

