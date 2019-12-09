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

$username = $this->element('MessagingCenter.user', [
    'user' => $message->get('fromUser') ? $message->fromUser : $message->from_user
]);
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4>
                <?= __d('Qobo/MessagingCenter', 'Message Box') ?>
                <?= $this->element('unread_count'); ?>
            </h4>
        </div>
    </div>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-3">
            <?= $this->Html->link(
                '<i class="fa fa-envelope-o" aria-hidden="true"></i> ' . __d('Qobo/MessagingCenter', 'Back to message'),
                ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'view', $message->id],
                ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
            ) ?>
            <?= $this->element('MessagingCenter.mailbox_details') ?>
            <?= $this->element('MessagingCenter.folders_list') ?>
        </div>
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __d('Qobo/MessagingCenter', 'Reply to: {0}', $username) ?></h3>
                </div>
                <?= $this->Form->create($message, ['type' => 'post']); ?>
                <div class="box-body">
                <?php
                echo $this->Form->control('to_user', [
                    'value' => $message->get('fromUser')->get('id'),
                    'label' => false,
                    'readonly' => true,
                    'type' => 'hidden'
                ]);
                echo $this->Form->control('to_user_label', [
                    'value' => $username,
                    'label' => false,
                    'readonly' => true
                ]);
                echo $this->Form->control('subject', [
                    'value' => 'Re: ' . $message->subject,
                    'label' => false,
                    'placeholder' => 'Subject:'
                ]);
                echo $this->Form->control('content', [
                    'value' => false,
                    'label' => false,
                    'placeholder' => 'Message:'
                ]);
                ?>
                </div>
                <div class="box-footer">
                    <div class="pull-right">
                        <?= $this->Form->button('<i class="fa fa-envelope-o"></i> ' . __d('Qobo/MessagingCenter', 'Send'), [
                            'class' => 'btn btn-primary'
                        ]); ?>
                    </div>
                    <?= $this->Form->button('<i class="fa fa-times"></i> ' . __d('Qobo/MessagingCenter', 'Discard'), [
                        'class' => 'btn btn-default',
                        'type' => 'reset'
                    ]); ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</section>
