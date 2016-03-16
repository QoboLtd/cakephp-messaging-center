<?php
use \Cake\Utility\Inflector;
?>

<div class="row">
    <div class="col-xs-12">
        <p class="text-right">
            <?php echo $this->Html->link(
                __('Create'),
                ['action' => 'create'],
                ['class' => 'btn btn-primary']
            ); ?>
        </p>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id'); ?></th>
                    <th><?= $this->Paginator->sort('from_user'); ?></th>
                    <th><?= $this->Paginator->sort('to_user'); ?></th>
                    <th><?= $this->Paginator->sort('subject'); ?></th>
                    <th><?= $this->Paginator->sort('date_sent'); ?></th>
                    <th><?= $this->Paginator->sort('status'); ?></th>
                    <th><?= $this->Paginator->sort('related_model'); ?></th>
                    <th class="actions"><?= __('Actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= h($message->id) ?></td>
                    <td>
                        <?= $message->has('user') ? $this->Html->link($message->user->username, ['controller' => 'Users', 'action' => 'view', $message->user->id]) : '' ?>
                    </td>
                    <td><?= h($message->to_user) ?></td>
                    <td><?= h($message->subject) ?></td>
                    <td><?= h($message->date_sent) ?></td>
                    <td><?= h($message->status) ?></td>
                    <td><?= h($message->related_model) ?></td>
                    <td class="actions">
                        <?= $this->Html->link('', ['action' => 'view', $message->id], ['title' => __('View'), 'class' => 'btn btn-default glyphicon glyphicon-eye-open']) ?>
                        <?= $this->Html->link('', ['action' => 'edit', $message->id], ['title' => __('Edit'), 'class' => 'btn btn-default glyphicon glyphicon-pencil']) ?>
                        <?= $this->Form->postLink('', ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->prev('< ' . __('previous')) ?>
        <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
        <?= $this->Paginator->next(__('next') . ' >') ?>
    </ul>
    <p><?= $this->Paginator->counter() ?></p>
</div>