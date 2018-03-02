<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$replySmBtn = $this->Html->link(
    '<i class="fa fa-reply"></i>',
    ['action' => 'reply', $message->id],
    ['title' => __('Reply'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
);
$deleteSmBtn = $this->Form->postLink(
    '<i class="fa fa-trash-o"></i>',
    ['action' => 'delete', $message->id],
    ['title' => __('Delete'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
);
$archiveSmBtn = $this->Form->postLink(
    '<i class="fa fa-archive"></i>',
    ['action' => 'archive', $message->id],
    ['title' => __('Archive'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
);
$restoreSmBtn = $this->Form->postLink(
    '<i class="fa fa-undo"></i>',
    ['action' => 'restore', $message->id],
    ['title' => __('Restore'), 'class' => 'btn btn-default btn-sm', 'escape' => false]
);
$replyBtn = $this->Html->link(
    '<i class="fa fa-reply"></i> ' . __('Reply'),
    ['action' => 'reply', $message->id],
    ['title' => __('Reply'), 'class' => 'btn btn-default', 'escape' => false]
);
$deleteBtn = $this->Form->postLink(
    '<i class="fa fa-trash-o"></i> ' . __('Delete'),
    ['action' => 'delete', $message->id],
    ['title' => __('Delete'), 'class' => 'btn btn-default', 'escape' => false]
);
$archiveBtn = $this->Form->postLink(
    '<i class="fa fa-archive"></i> ' . __('Archive'),
    ['action' => 'archive', $message->id],
    ['title' => __('Archive'), 'class' => 'btn btn-default', 'escape' => false]
);
$restoreBtn = $this->Form->postLink(
    '<i class="fa fa-undo"></i> ' . __('Restore'),
    ['action' => 'restore', $message->id],
    ['title' => __('Restore'), 'class' => 'btn btn-default', 'escape' => false]
);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Read Message'); ?></h4>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-3">
            <?= $this->Html->link(
                '<i class="fa fa-pencil" aria-hidden="true"></i> ' . __('Compose'),
                ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'compose'],
                ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
            ); ?>
            <?= $this->element('MessagingCenter.folders_list') ?>
        </div>
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Read Message'); ?></h3>
                </div>
                <div class="box-body no-padding">
                    <div class="mailbox-read-info">
                        <h3><?= h($message->subject) ?></h3>
                        <?php $fromUser = !empty($message->fromUser) ? $message->fromUser : $message->from_user; ?>
                        <h5>From: <?= $this->element('MessagingCenter.user', ['user' => $fromUser]) ?>
                        <span class="mailbox-read-time pull-right"><?= h($message->date_sent->i18nFormat([\IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT])) ?></span></h5>
                    </div>
                    <div class="mailbox-controls with-border text-center">
                        <div class="btn-group">
                        <?php
                        switch ($folder) {
                            case 'inbox':
                                echo $deleteSmBtn;
                                echo $replySmBtn;
                                echo $archiveSmBtn;
                                break;

                            case 'archived':
                                echo $deleteSmBtn;
                                echo $replySmBtn;
                                echo $restoreSmBtn;
                                break;

                            case 'trash':
                                echo $replySmBtn;
                                echo $restoreSmBtn;
                                break;
                        }
                        ?>
                        </div>
                    </div>
                    <div class="mailbox-read-message">
                        <?= $this->Text->autoParagraph($message->content); ?>
                    </div>
                </div>
                <div class="box-footer">
                    <div class="pull-right">
                        <?= $replyBtn; ?>
                    </div>
                    <?php
                    switch ($folder) {
                        case 'inbox':
                            echo $deleteBtn;
                            echo ' ';
                            echo $archiveBtn;
                            break;

                        case 'archived':
                            echo $deleteBtn;
                            echo ' ';
                            echo $restoreBtn;
                            break;

                        case 'trash':
                            echo $restoreBtn;
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
