<?php
use \Cake\Utility\Inflector;
echo $this->Html->css('MessagingCenter.style');
echo $this->Html->script('MessagingCenter.script', ['block' => 'scriptBottom']);
?>

<div class="row">
    <div class="col-xs-2">
        <?php echo $this->Html->link(
            __('Compose'),
            ['action' => 'create'],
            ['class' => 'btn btn-primary btn-block']
        ); ?>
        <?= $this->element('MessagingCenter.folders') ?>
    </div>
    <div class="col-xs-10">
        <div class="row">
            <div class="col-xs-12">
                <div class="paginator message-paginator">
                    <ul class="pagination pagination-sm pull-right">
                        <?= $this->Paginator->prev('<') ?>
                        <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
                        <?= $this->Paginator->next('>') ?>
                    </ul>
                    <span class="pull-right"><?= $this->Paginator->counter(['format' => 'range']) ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <table id="inbox-table" class="table table-hover inbox-table">
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                        <tr class="<?= 'new' !== $message->status ?: 'unread'; ?>" data-url="<?= $this->Url->build(['action' => 'view', $message->id]) ?>">
                            <td>
                                <?= $message->has('user') ? $message->user->username : '' ?>
                            </td>
                            <td><?= h($message->subject) ?></td>
                            <td><?= h($message->date_sent) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>