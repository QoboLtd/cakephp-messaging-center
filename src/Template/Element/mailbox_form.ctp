<?php
use Cake\Core\Configure;

$incomingTransports = (array)Configure::read('MessagingCenter.incomingTransports');
$outgoingTransports = (array)Configure::read('MessagingCenter.outgoingTransports');

echo $this->Html->css(
    [
        'AdminLTE./bower_components/select2/dist/css/select2.min',
        'Qobo/Utils.select2-bootstrap.min',
        'Qobo/Utils.select2-style'
    ],
    [
        'block' => 'css'
    ]
);
echo $this->Html->script(
    [
        'AdminLTE./bower_components/select2/dist/js/select2.full.min',
        'Qobo/Utils.select2.init'
    ],
    [
        'block' => 'scriptBottom'
    ]
);

?>
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
                <?= $this->Form->control('user_id', ['options' => $users]); ?>
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
    </div>
</div>
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= __('Incoming Settings');?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('incoming_transport', ['options' => $incomingTransports]); ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->select('default_folder',
                    $folder_list,
                    array(
                        'multiple' => 'multiple',
                        'class' => 'select2',
                        'val' => $selected_folder_list
                        )
                    );
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('IncomingSettings.host', ['label' => 'Host address']); ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('IncomingSettings.port', ['label' => 'Port']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?php
                    $label = $this->Form->label('Use SSL');
                    echo $this->Form->control('IncomingSettings.use_ssl', [
                        'type' => 'checkbox',
                        'label' => false,
                        'class' => 'square',
                        'templates' => [
                            'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
                        ]
                    ]);
                ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?php
                    $label = $this->Form->label('No Validate SSL Certificate');
                    echo $this->Form->control('IncomingSettings.no_validate_ssl_cert', [
                        'type' => 'checkbox',
                        'label' => false,
                        'class' => 'square',
                        'templates' => [
                            'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
                        ]
                    ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('IncomingSettings.username'); ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('IncomingSettings.password'); ?>
            </div>
        </div>
    </div>
</div>
<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title"><?= __('Outgoing Settings');?></h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('outgoing_transport', ['options' => $outgoingTransports]); ?>
            </div>
            <div class="col-xs-12 col-md-6"></div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('OutgoingSettings.host', ['label' => 'Host address']); ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('OutgoingSettings.port', ['label' => 'Port']); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?php
                    $label = $this->Form->label('Use SSL');
                    echo $this->Form->control('OutgoingSettings.use_ssl', [
                        'type' => 'checkbox',
                        'label' => false,
                        'class' => 'square',
                        'templates' => [
                            'inputContainer' => '<div class="{{required}}">' . $label . '<div class="clearfix"></div>{{content}}</div>'
                        ]
                    ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('OutgoingSettings.username'); ?>
            </div>
            <div class="col-xs-12 col-md-6">
                <?= $this->Form->control('OutgoingSettings.password'); ?>
            </div>
        </div>
    </div>
</div>
<?= $this->Form->button(__('Submit')) ?>&nbsp;
<?= $this->Html->link(__('Cancel'), [
    'plugin' => 'MessagingCenter',
    'controller' => 'Mailboxes',
    'action' => 'index'
], ['class' => 'btn btn-gray']) ?>
<?= $this->Form->end() ?>
