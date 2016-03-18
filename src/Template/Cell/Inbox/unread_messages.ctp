<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fa fa-envelope"></i>
        <?php if ((bool)$count) : ?>
            <sup><?= $this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}']); ?></sup>
        <?php endif; ?>
        <i class="fa fa-caret-down"></i>
    </a>
    <ul class="dropdown-menu dropdown-messages">
        <?php foreach ($messages as $message) : ?>
        <li>
            <a href="<?= $this->Url->build(['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'view', $message->id]); ?>">
                <div>
                    <strong><?= $message->fromUser->username ?></strong>
                    <span class="pull-right text-muted">
                        <em><?= $message->date_sent ?></em>
                    </span>
                </div>
                <div><?= $this->Text->truncate(
                    $message->content,
                    (int)$contentLength,
                    [
                        'ellipsis' => '...',
                        'exact' => false
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