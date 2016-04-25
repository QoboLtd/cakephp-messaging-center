<?php
echo $this->Html->css('MessagingCenter.style');
echo $this->Html->script('MessagingCenter.script', ['block' => 'scriptBottom']);
?>

<div class="row">
    <div class="col-xs-12 col-md-4 col-lg-3">
        <?= $this->element('MessagingCenter.sidebar') ?>
    </div>
    <div class="col-xs-12 col-md-8 col-lg-9">
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
                <?php if (0 < $messages->count()) : ?>
                <table id="folder-table" class="table table-hover folder-table">
                    <tbody>
                        <?php foreach ($messages as $message): ?>
                        <?php
                        $readClass = '';
                        if ('new' === $message->status && 'sent' !== $folder) {
                            $readClass = ' unread ';
                        }
                        $messageUser = 'fromUser';
                        if ('sent' === $folder) {
                            $messageUser = 'toUser';
                        }
                        ?>
                        <tr class="<?= $readClass ?>" data-url="<?= $this->Url->build(['action' => 'view', $message->id]) ?>">
                            <td>
                                <?= $message->has($messageUser) ? $message->{$messageUser}->username : '' ?>
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
                <?php else : ?>
                <div class="well">
                    <p class="h4 text-muted"><?= __('You don\'t have any messages here...') ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>