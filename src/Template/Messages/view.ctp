<?php
echo $this->Html->css('MessagingCenter.style');
?>

<div class="row">
    <div class="col-xs-2">
        <?= $this->element('MessagingCenter.sidebar') ?>
    </div>
    <div class="col-xs-10">
        <div class="row">
            <div class="col-xs-12">
                <div class="actions text-right">
                    <?= $this->Form->postLink('', ['action' => 'delete', $message->id], ['confirm' => __('Are you sure you want to delete # {0}?', $message->id), 'title' => __('Delete'), 'class' => 'btn btn-default glyphicon glyphicon-trash']) ?>
                </div>
                <hr />
                <h2><?= h($message->subject) ?></h2>
                <hr />
            </div>
        </div>
        <div class="row">
            <div class="col-xs-10">
                <p>
                    <strong><?= $message->has('from_user') ? h($message->from_user->username) : '' ?></strong> to
                    <strong><?= $message->has('to_user') ? h($message->to_user->username) : '' ?></strong> on
                    <?= h($message->date_sent) ?>
                </p>
                <hr />
                <div><?= $this->Text->autoParagraph(h($message->content)); ?></div>
            </div>
        </div>
    </div>
</div>
