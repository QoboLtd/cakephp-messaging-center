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

use Cake\Utility\Inflector;
use MessagingCenter\Model\Table\MailboxesTable;

$userField = MailboxesTable::FOLDER_SENT === $folderName ? 'to' : 'from';

?>
<section class="content-header">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <h4>
                <a href="/messaging-center/mailboxes/"><?= __('Mailboxes') ?></a>
                »
                <?= $mailbox->get('name') ?>
                <?= $this->element('unread_count'); ?>
            </h4>
        </div>
    </div>
</section>

<section class="content">
    <div class="row">
        <?= $this->element('MessagingCenter.common_sidebar'); ?>
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Inflector::humanize($folderName); ?></h3>
                </div>
                <div class="box-body no-padding">
                    <div class="mailbox-controls">
                        <a href="<?= $this->request->getAttribute('here'); ?>" type="button" class="btn btn-default btn-sm">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <div class="pull-right">
                            <?= $this->Paginator->counter(['format' => '{{start}}-{{end}}/{{count}}']) ?>
                            <div class="btn-group">
                                <?= $this->Paginator->prev('<i class="fa fa-chevron-left"></i>', [
                                    'escape' => false,
                                    'templates' => [
                                        'prevActive' => '<a type="button" class="btn btn-default btn-sm prev" rel="prev" href="{{url}}">{{text}}</a>',
                                        'prevDisabled' => '<a type="button" class="btn btn-default btn-sm prev disabled" href="" onclick="return false;">{{text}}</a>'
                                    ]
                                ]); ?>
                                <?= $this->Paginator->next('<i class="fa fa-chevron-right"></i>', [
                                    'escape' => false,
                                    'templates' => [
                                        'nextActive' => '<a type="button" class="btn btn-default btn-sm next" rel="next" href="{{url}}">{{text}}</a>',
                                        'nextDisabled' => '<a type="button" class="btn btn-default btn-sm next disabled" href="" onclick="return false;">{{text}}</a>'
                                    ]
                                ]); ?>
                            </div>
                        </div>
                    </div>
                    <?php if (0 < $messages->count()) : ?>
                    <div class="table-responsive mailbox-messages">
                        <table id="folder-table" class="table table-hover table-striped">
                            <thead>
                                <th></th>
                                <th><?= Inflector::humanize($userField); ?></th>
                                <th><?= __('Subject') ?></th>
                                <th><?= __('Date') ?></th>
                            </thead>
                            <tbody>
                            <?php foreach ($messages as $message) : ?>
                                <?php
                                $messageUrl = $this->Url->build([
                                    'plugin' => 'MessagingCenter',
                                    'controller' => 'Messages',
                                    'action' => 'view',
                                    $message->id
                                ]);

                                ?>
                                <tr>
                                    <td class="mailbox-read text-center">
                                        <?php if ('new' === $message->status && MailboxesTable::FOLDER_SENT !== $folderName) : ?>
                                            <small><i class="fa fa-envelope-o" title="unread"></i></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="mailbox-name">
                                        <a href="<?= $messageUrl ?>">
                                            <?= $this->element('MessagingCenter.Messages/' . $userField, ['message' => $message]) ?>
                                        </a>
                                    </td>
                                    <td class="mailbox-subject">
                                        <a href="<?= $messageUrl ?>">
                                            <?= $this->element('MessagingCenter.Messages/subject', ['message' => $message]) ?>
                                        </a>
                                    </td>
                                    <td class="mailbox-date">
                                        <?= h($this->Time->timeAgoInWords($message->date_sent, [
                                            'format' => 'yyyy-MM-dd HH:mm'
                                        ])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else : ?>
                    <p class="text-muted text-center"><?= __('You don\'t have any messages here...') ?></p>
                    <?php endif; ?>
                </div>
                <div class="box-footer no-padding">
                    <div class="mailbox-controls">
                        <a href="<?= $this->request->getAttribute('here'); ?>" type="button" class="btn btn-default btn-sm">
                            <i class="fa fa-refresh"></i>
                        </a>
                        <div class="pull-right">
                            <?= $this->Paginator->counter(['format' => '{{start}}-{{end}}/{{count}}']) ?>
                            <div class="btn-group">
                                <?= $this->Paginator->prev('<i class="fa fa-chevron-left"></i>', [
                                    'escape' => false,
                                    'templates' => [
                                        'prevActive' => '<a type="button" class="btn btn-default btn-sm prev" rel="prev" href="{{url}}">{{text}}</a>',
                                        'prevDisabled' => '<a type="button" class="btn btn-default btn-sm prev disabled" href="" onclick="return false;">{{text}}</a>'
                                    ]
                                ]); ?>
                                <?= $this->Paginator->next('<i class="fa fa-chevron-right"></i>', [
                                    'escape' => false,
                                    'templates' => [
                                        'nextActive' => '<a type="button" class="btn btn-default btn-sm next" rel="next" href="{{url}}">{{text}}</a>',
                                        'nextDisabled' => '<a type="button" class="btn btn-default btn-sm next disabled" href="" onclick="return false;">{{text}}</a>'
                                    ]
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
