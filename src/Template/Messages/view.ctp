<?php
echo $this->Html->css('MessagingCenter.style');

$toUserId = $message->has('toUser') ? h($message->toUser->id) : '';
?>

<div class="row">
    <div class="col-xs-12 col-md-4 col-lg-3">
        <?= $this->element('MessagingCenter.sidebar') ?>
    </div>
    <div class="col-xs-12 col-md-8 col-lg-9">
        <div class="row">
            <div class="col-xs-12">
                <div class="message-actions">
                <?php
                    $replyBtn = $this->Html->link(
                        '',
                        ['action' => 'reply', $message->id],
                        ['title' => __('Reply'), 'class' => 'btn btn-default glyphicon glyphicon-share-alt']
                    );
                    $deleteBtn = $this->Form->postLink(
                        '',
                        ['action' => 'delete', $message->id],
                        ['title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash pull-right']
                    );
                    $archiveBtn = $this->Form->postLink(
                        '',
                        ['action' => 'archive', $message->id],
                        ['title' => __('Archive'), 'class' => 'btn btn-default glyphicon glyphicon-folder-open pull-right']
                    );
                    $restoreBtn = $this->Form->postLink(
                        '',
                        ['action' => 'restore', $message->id],
                        ['title' => __('Restore'), 'class' => 'btn btn-default glyphicon glyphicon-folder-close pull-right']
                    );
                    switch ($folder) {
                        case 'inbox':
                            echo $replyBtn;
                            echo $deleteBtn;
                            echo $archiveBtn;
                            break;

                        case 'archived':
                            echo $replyBtn;
                            echo $deleteBtn;
                            echo $restoreBtn;
                            break;

                        case 'trash':
                            echo $replyBtn;
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
            <div class="col-xs-12">
                <p>
                    <strong><?= $message->has('fromUser') ? h($message->fromUser->username) : '' ?></strong> to
                    <strong><?= $message->has('toUser') ? h($message->toUser->username) : '' ?></strong> on
                    <?= h($message->date_sent) ?>
                </p>
                <hr />
                <div><?= $this->Text->autoParagraph($message->content); ?></div>
            </div>
        </div>
    </div>
</div>
