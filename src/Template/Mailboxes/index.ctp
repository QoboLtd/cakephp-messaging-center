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
$options['title'] = 'Mailboxes';
?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4><?= $options['title'] ?></h4>
        </div>
        <div class="col-xs-12 col-md-6">
            <div class="pull-right">
                <div class="btn-group btn-group-sm">
                <?= $this->Html->link(
                    '<i class="fa fa-plus"></i>' . __d('Qobo/MessagingCenter', 'Add'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Mailboxes', 'action' => 'add'],
                    ['class' => 'btn btn-default', 'escape' => false]
                );?>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-body">
            <div class="table-responsive">
                <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
                    <thead>
                        <tr>
                            <th scope="col"><?= $this->Paginator->sort('name') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('type') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('active') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                            <th scope="col" class="actions"><?= __d('Qobo/MessagingCenter', 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mailboxes as $mailbox) : ?>
                        <tr>
                            <td><?= h($mailbox->get('name')) ?></td>
                            <td><?= h($mailbox->get('type')) ?></td>
                            <td><?= h($mailbox->active ? __d('Qobo/MessagingCenter', 'Yes') : __('No')) ?></td>
                            <td><?= h($mailbox->created->i18nFormat('yyyy-MM-dd HH:mm')) ?></td>
                            <td class="actions btn-group btn-group-xs" role="group">
                                <?= $this->Html->link(
                                    '<i class="fa fa-eye"></i>',
                                    ['action' => 'view', $mailbox->get('id')],
                                    ['class' => 'btn btn-default', 'escape' => false, 'title' => __d('Qobo/MessagingCenter', 'View Mailboxes & Results')]
                                )?>
                                <?= $this->Html->link('<i class="fa fa-pencil"></i>', ['action' => 'edit', $mailbox->get('id')], ['class' => 'btn btn-default', 'escape' => false]) ?>
                                <?= $this->Form->postLink('<i class="fa fa-trash"></i>', ['action' => 'delete', $mailbox->get('id')], ['class' => 'btn btn-default', 'escape' => false, 'confirm' => __d('Qobo/MessagingCenter', 'Are you sure you want to delete # {0}?', $mailbox->get('id'))]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="paginator">
                <ul class="pagination">
                    <?= $this->Paginator->first('<< ' . __d('Qobo/MessagingCenter', 'first')) ?>
                    <?= $this->Paginator->prev('< ' . __d('Qobo/MessagingCenter', 'previous')) ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next(__d('Qobo/MessagingCenter', 'next') . ' >') ?>
                    <?= $this->Paginator->last(__d('Qobo/MessagingCenter', 'last') . ' >>') ?>
                </ul>
                <p><?= $this->Paginator->counter(['format' => __d('Qobo/MessagingCenter', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
            </div>
        </div>
    </div>
</section>
