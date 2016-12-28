<?php
use Cake\Utility\Inflector;

echo $this->Html->css('MessagingCenter.style');
echo $this->Html->script('MessagingCenter.script', ['block' => 'scriptBottom']);

$unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}'])->render();
?>
<section class="content-header">
    <h1>Mailbox <small><?= 0 < $unreadCount ? $unreadCount . ' ' . __('new messages') : ''; ?></small></h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-3">
            <?= $this->element('MessagingCenter.sidebar') ?>
        </div>
        <div class="col-md-9">
            <div class="row">
                <div class="col-xs-12">
                    <div class="paginator message-paginator">
                        <ul class="pagination pagination-sm pull-right">
                            <?= $this->Paginator->prev('<') ?>
                            <?= $this->Paginator->numbers(['before' => '', 'after' => '']) ?>
                            <?= $this->Paginator->next('>') ?>
                        </ul>
                        <span class="pull-right"><?= $this->Paginator->counter(['format' => 'range']) ?></span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <?php if (0 < $messages->count()) : ?>

                    <table id="folder-table" class="table table-hover folder-table">
                        <thead>
                            <tr>
                                <th><?php
                                    echo $this->Paginator->sort(
                                        'sent' === $folder ? 'toUser' : 'fromUser',
                                        'sent' === $folder ? __('To') : __('From')
                                    );
                                ?></th>
                                <th><?php echo $this->Paginator->sort('subject'); ?></th>
                                <th><?php echo $this->Paginator->sort('sent'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($messages as $message) : ?>
                            <?php
                            $messageUser = 'sent' === $folder ? 'toUser' : 'fromUser';
                            $messageUser = !empty($message->{$messageUser}) ?
                                $message->{$messageUser} :
                                $message->{Inflector::underscore($messageUser)};
                            ?>
                            <?php
                            $readClass = '';
                            if ('new' === $message->status && 'sent' !== $folder) {
                                $readClass = ' unread ';
                            }
                            ?>
                            <tr
                                class="<?= $readClass ?>"
                                data-url="<?= $this->Url->build(['action' => 'view', $message->id]) ?>"
                            >
                                <td><?= $this->element('MessagingCenter.user', ['user' => $messageUser]) ?></td>
                                <td><?= h($message->subject) ?> -
                                    <span class="text-muted read">
                                        <?= $this->Text->truncate(
                                            $message->content,
                                            50,
                                            [
                                                'ellipsis' => '...',
                                                'exact' => false
                                            ]
                                        ); ?>
                                    </span>
                                </td>
                                <td><?= h($message->date_sent->i18nFormat('yyyy-MM-dd HH:mm')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else : ?>
                    <div class="well">
                        <p class="h4 text-muted"><?= __('You don\'t have any messages here...') ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>