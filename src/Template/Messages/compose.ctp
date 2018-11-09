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

use Cake\Core\Configure;

// enable typeahead library
echo $this->Html->script('MessagingCenter.bootstrap-typeahead.js', ['block' => 'script']);
echo $this->Html->script('MessagingCenter.typeahead', ['block' => 'script']);
echo $this->Html->scriptBlock(
    'messaging_center_typeahead.init(
        {
            min_length: "' . Configure::read('MessagingCenter.typeahead.min_length') . '",
            timeout: "' . Configure::read('MessagingCenter.typeahead.timeout') . '",
            api_token: "' . Configure::read('MessagingCenter.api.token') . '"
        }
    );',
    ['block' => 'scriptBotton']
);

$unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}'])->render();
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
                '<i class="fa fa-inbox" aria-hidden="true"></i> ' . __('Back to inbox'),
                ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'folder', 'inbox'],
                ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
            ); ?>
            <?= $this->element('MessagingCenter.folders_list') ?>
        </div>
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= __('Compose New {0}', ['Message']) ?></h3>
                </div>
                <?= $this->Form->create($message); ?>
                <div class="box-body">
                <?php
                echo $this->Form->control('to_user', [
                    'label' => false,
                    'name' => 'to_user_label',
                    'id' => 'to_user_label',
                    'type' => 'text',
                    'data-type' => 'typeahead',
                    'data-name' => 'to_user',
                    'autocomplete' => 'off',
                    'placeholder' => 'To:',
                    'data-url' => '/api/users/lookup.json'
                ]);
                echo $this->Form->control('to_user', ['type' => 'hidden']);
                echo $this->Form->control('subject', ['label' => false, 'placeholder' => 'Subject:']);
                echo $this->Form->control('content', ['label' => false, 'placeholder' => 'Message:']);
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
