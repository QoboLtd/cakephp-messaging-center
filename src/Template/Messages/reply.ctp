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

$unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}'])->render();

$username = $this->element('MessagingCenter.user', [
    'user' => $message->get('fromUser') ? $message->fromUser : $message->from_user
]);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4>
                <?= __('Message Box') ?>
                <small><?= 0 < $unreadCount ? $unreadCount . ' ' . __('new messages') : '' ?></small>
            </h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-3">
            <?= $this->Html->link(
                    '<i class="fa fa-envelope-o" aria-hidden="true"></i> ' . __('Back to message'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'view', $message->id],
                    ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
                ) ?>
            <?= $this->element('MessagingCenter.folders_list') ?>
        </div>
        <div class="col-md-9">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Reply to: {0}', [$message->subject]) ?></h3>
                </div>
                <?= $this->Form->create($message); ?>
                <div class="box-body">
                <?php
                echo $this->Form->input('to_user_label', [
                    'value' => $username,
                    'label' => false,
                    'readonly' => true
                ]);
                echo $this->Form->input('subject', [
                    'value' => 'Re: ' . $message->subject,
                    'label' => false,
                    'placeholder' => 'Subject:'
                ]);
                echo $this->Form->input('content', [
                    'value' => false,
                    'label' => false,
                    'placeholder' => 'Message:'
                ]);
                ?>
                </div>
                <div class="box-footer">
                    <div class="pull-right">
                        <?= $this->Form->button('<i class="fa fa-envelope-o"></i> ' . __('Send'), [
                            'class' => 'btn btn-primary'
                        ]); ?>
                    </div>
                    <?= $this->Form->button('<i class="fa fa-times"></i> ' . __('Discard'), [
                        'class' => 'btn btn-default',
                        'type' => 'reset'
                    ]); ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</section>
