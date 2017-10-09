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
                    '<i class="fa fa-pencil" aria-hidden="true"></i> ' . __('Compose'),
                    ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'compose'],
                    ['class' => 'btn btn-primary btn-block margin-bottom', 'escape' => false]
                ); ?>
            <?= $this->element('MessagingCenter.folders_list') ?>
        </div>
        <div class="col-md-9">
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Inflector::humanize($folder); ?></h3>
                </div>
                <div class="box-body no-padding">
                    <div class="mailbox-controls">
                        <a href="<?= $this->request->here; ?>" type="button" class="btn btn-default btn-sm">
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
                                <th><?= 'sent' === $folder ? __('To') : __('From') ?></th>
                                <th><?= __('Subject') ?></th>
                                <th><?= __('Date') ?></th>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message) : ?>
                                <?php
                                $messageUser = 'sent' === $folder ? 'toUser' : 'fromUser';
                                $messageUser = !empty($message->{$messageUser}) ?
                                    $message->{$messageUser} :
                                    $message->{Inflector::underscore($messageUser)};

                                $messageUrl = $this->Url->build([
                                    'plugin' => 'MessagingCenter',
                                    'controller' => 'Messages',
                                    'action' => 'view',
                                    $message->id
                                ]);
                                ?>
                                <tr>
                                    <td class="mailbox-read text-center">
                                        <?php if ('new' === $message->status && 'sent' !== $folder) : ?>
                                        <small><i class="fa fa-envelope-o" title="unread"></i></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="mailbox-name">
                                        <a href="<?= $messageUrl ?>">
                                            <?= $this->element('MessagingCenter.user', ['user' => $messageUser]) ?>
                                        </a>
                                    </td>
                                    <td class="mailbox-subject"><?= h($message->subject) ?></td>
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
                        <a href="<?= $this->request->here; ?>" type="button" class="btn btn-default btn-sm">
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
