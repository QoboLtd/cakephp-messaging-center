<?php
echo $this->Html->css('MessagingCenter.style');
echo $this->Html->script('MessagingCenter.script', ['block' => 'scriptBottom']);
?>

<div class="row">
    <div class="col-xs-2">
        <?= $this->element('MessagingCenter.sidebar') ?>
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
                                <?= $message->has('from_user') ? $message->from_user->username : '' ?>
                            </td>
                            <td><?= h($message->subject) ?> -
                                <span class="text-muted">
                                    <?= $this->Text->truncate(
                                        $message->content,
                                        50,
                                        [
                                            'ellipsis' => '...',
                                            'exact' => false
                                        ]
                                    ); ?>
                                </span>
                            </td>
                            <td><?= h($message->date_sent) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>