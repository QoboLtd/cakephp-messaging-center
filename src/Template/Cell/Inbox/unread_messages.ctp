<?= $this->Html->css('MessagingCenter.notifications'); ?>

<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fa fa-envelope"></i>
        <?php if ((bool)$count) : ?>
        <div class="noti_bubble"><?= $this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}']); ?></div>
        <?php endif; ?>
        <i class="fa fa-caret-down"></i>
    </a>
    <ul class="dropdown-menu dropdown-messages">
        <?php foreach ($messages as $message) : ?>
        <?php $fromUser = !empty($message->fromUser) ? $message->fromUser : $message->from_user; ?>
        <li>
            <a href="<?= $this->Url->build(['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'view', $message->id]); ?>">
                <div>
                    <strong><?= $this->element('MessagingCenter.user', ['user' => $fromUser]) ?></strong>
                    <span class="pull-right text-muted">
                        <em><?= h($message->date_sent->i18nFormat('yyyy-MM-dd HH:mm')) ?></em>
                    </span>
                </div>
                <div><?= $this->Text->truncate(
                            $this->Text->stripLinks($message->content),
                            (int)$contentLength,
                            [
                            'ellipsis' => '...',
                            'exact' => true,
                            'html' => true
                            ]
                        ); ?></div>
            </a>
        </li>
        <li class="divider"></li>
        <?php endforeach; ?>

        <li>
            <?= $this->Html->link(
                '<strong>Read All Messages</strong>',
                ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'folder', 'inbox'],
                ['class' => 'text-center', 'escape' => false]
            ); ?>
        </li>
    </ul>
    <!-- /.dropdown-messages -->
</li>
<!-- /.dropdown -->