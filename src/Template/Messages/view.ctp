<?php
echo $this->Html->css('MessagingCenter.style');

$toUserId = $message->has('toUser') ? h($message->toUser->id) : '';
?>

<div class="row">
    <div class="col-xs-2">
        <?= $this->element('MessagingCenter.sidebar') ?>
    </div>
    <div class="col-xs-10">
        <div class="row">
            <div class="col-xs-12">
                <div class="message-actions text-right">
                <?php
                    $deleteBtn = $this->Form->postLink(
                        '',
                        ['action' => 'delete', $message->id],
                        ['title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']
                    );
                    $archiveBtn = $this->Form->postLink(
                        '',
                        ['action' => 'archive', $message->id],
                        ['title' => __('Archive'), 'class' => 'btn btn-default glyphicon glyphicon-folder-open']
                    );
                    $restoreBtn = $this->Form->postLink(
                        '',
                        ['action' => 'restore', $message->id],
                        ['title' => __('Restore'), 'class' => 'btn btn-default glyphicon glyphicon-folder-close']
                    );
                    switch ($folder) {
                        case 'inbox':
                            echo $archiveBtn;
                            echo $deleteBtn;
                            break;

                        case 'archived':
                            echo $deleteBtn;
                            echo $restoreBtn;
                            break;

                        case 'trash':
                            echo $restoreBtn;
                            break;
                    }
                ?>
                </div>
                <hr />
                <h2><?= h($message->subject) ?></h2>
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-10">
                <p>
                    <strong><?= $message->has('fromUser') ? h($message->fromUser->username) : '' ?></strong> to
                    <strong><?= $message->has('toUser') ? h($message->toUser->username) : '' ?></strong> on
                    <?= h($message->date_sent) ?>
                </p>
                <hr />
                <div><?= $this->Text->autoParagraph(h($message->content)); ?></div>
            </div>
        </div>
    </div>
</div>
