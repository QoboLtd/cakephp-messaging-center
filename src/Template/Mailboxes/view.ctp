<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $mailbox
 */

echo $this->Html->css([
    'AdminLTE./bower_components/morris.js/morris',
], [
    'block' => 'scriptBottom'
]);

echo $this->Html->script(
    [
        'AdminLTE./bower_components/morris.js/morris.min',
    ],
    [
        'block' => 'scriptBottom'
    ]
);

$options['title'] = $this->Html->link(__('Mailbox'), ['controller' => 'Mailbox', 'action' => 'index']);
$options['title'] .= ' &raquo; ' . $mailbox->get('name');
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
            <div class="btn-group btn-group-sm" role="group">
                <?= $this->Html->link(
                    '<i class="fa fa-pencil"></i> ' . __('Edit'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Mailbox', 'action' => 'edit', $mailbox->get('id')],
                    ['class' => 'btn btn-default', 'escape' => false]
                ); ?>
                <?= $this->Form->postLink(
                    '<i class="fa fa-trash"></i> ' . __('Delete'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Mailbox', 'action' => 'delete', $mailbox->get('id')],
                    ['escape' => false, 'class' => 'btn btn-default', 'confirm' => __('Are you sure you want to delete # {0}', $mailbox->get('name'))]
                ); ?>
            </div>
            </div>
        </div>
    </div>
</section>

<section class="content">
       <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __('Details'); ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('User') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('user_id')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Active') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('active')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
            </div>
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Name') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('name')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Type') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('type')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
            </div>
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Incoming Transport') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('incoming_transport')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Incoming Settings') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('incoming_settings')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
            </div>
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Outgoing Transport') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('outgoing_transport')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Outgoing Settings') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('outgoing_settings')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
            </div>
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Created') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('created')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __('Modified') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($mailbox->get('modified')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
            </div>
        </div>
    </div>
    <div class="nav-tabs-custom">
        <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <li role="presentation">
                <a href="#manage-folders-sections" aria-controls="manage-content" role="tab" data-toggle="tab">
                    <i class="fa fa-list-ul"></i> <i class="fa question-circle"></i> <?= __('Folders'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane" id="manage-folders-sections">
                <?= $this->element('folders_list', ['mailbox' => $mailbox]); ?>
            </div>
        </div>
    </div>

</section>
