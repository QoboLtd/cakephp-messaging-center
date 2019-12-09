<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $folder
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

$options['title'] = $this->Html->link(__d('Qobo/MessagingCenter', 'Folder'), ['controller' => 'Folders', 'action' => 'index']);
$options['title'] .= ' &raquo; ' . $folder->get('name');
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
                    '<i class="fa fa-pencil"></i> ' . __d('Qobo/MessagingCenter', 'Edit'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Mailbox', 'action' => 'edit', $folder->get('id')],
                    ['class' => 'btn btn-default', 'escape' => false]
                ); ?>
                <?= $this->Form->postLink(
                    '<i class="fa fa-trash"></i> ' . __d('Qobo/MessagingCenter', 'Delete'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Mailbox', 'action' => 'delete', $folder->get('id')],
                    ['escape' => false, 'class' => 'btn btn-default', 'confirm' => __d('Qobo/MessagingCenter', 'Are you sure you want to delete # {0}', $folder->get('name'))]
                ); ?>
            </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= __d('Qobo/MessagingCenter', 'Details'); ?></h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __d('Qobo/MessagingCenter', 'Name') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($folder->get('name')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
                <div class="col-xs-4 col-md-2 text-right">
                    <strong><?= __d('Qobo/MessagingCenter', 'Type') ?>:</strong>
                </div>
                <div class="col-xs-8 col-md-4">
                    <?= h($folder->get('type')) ?>
                </div>
                <div class="clearfix visible-xs visible-sm"></div>
            </div>
        </div>
    </div>
    <div class="nav-tabs-custom">
        <ul id="relatedTabs" class="nav nav-tabs" role="tablist">
            <li role="presentation">
                <a href="#manage-folders-sections" aria-controls="manage-content" role="tab" data-toggle="tab">
                    <i class="fa fa-list-ul"></i> <i class="fa question-circle"></i> <?= __d('Qobo/MessagingCenter', 'Messages'); ?>
                </a>
            </li>
        </ul>
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane" id="manage-folders-sections">
                <?= $this->element('messages_list', ['folder' => $folder]); ?>
            </div>
        </div>
    </div>

</section>
