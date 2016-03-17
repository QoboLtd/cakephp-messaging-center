<?php
$actions = [
    'inbox' => __('Inbox'),
    'sent' => __('Sent'),
    'trash' => __('Trash')
];
$currAction = $this->request->params['action'];
?>
<div class="message-folders">
    <p class="text-uppercase text-muted">
        Folder <a href="" class="pull-right"><i class="fa fa-refresh"></i></a>
    </p>
    <div class="list-group">
        <?php foreach ($actions as $action => $label) : ?>
            <?php
                if ('inbox' === $action) {
                    $cell = $this->cell('MessagingCenter.Inbox::unreadCount');
                    $label .= ' ' . $cell;
                }
            ?>
            <?= $this->Html->link(
                $label, ['action' => $action], [
                    'escape' => false,
                    'class' => 'list-group-item ' . ($action !== $currAction ?: 'active')
                ]); ?>
        <?php endforeach; ?>
    </div>
</div>