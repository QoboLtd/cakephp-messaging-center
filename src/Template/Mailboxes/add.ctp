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
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= __('Create {0}', ['Mailbox']);?></h4>
        </div>
    </div>
</section>
<section class="content">
    <?= $this->Form->create($mailbox) ?>
    <div class="box box-solid">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Details');?></h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('name'); ?>
                </div>
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('type', ['options' => $types]); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('user_id'); ?>
                </div>
                <div class="col-xs-12 col-md-6">
                    <?php
                    $label = $this->Form->label('active');
                    echo $this->Form->control('active', [
                        'type' => 'checkbox',
                        'class' => 'square',
                        'label' => false,
                        'templates' => [
                            'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
                        ]
                    ]);
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('incoming_transport', ['options' => $incomingTransports]); ?>
                </div>
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('incoming_settings'); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('outgoing_transport', ['options' => $outgoingTransports]); ?>
                </div>
                <div class="col-xs-12 col-md-6">
                    <?= $this->Form->control('outgoing_settings'); ?>
                </div>
            </div>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</section>
