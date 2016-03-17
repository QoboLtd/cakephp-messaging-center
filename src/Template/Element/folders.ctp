<?php
$actions = [
    'inbox' => [
        'label' => __('Inbox'),
        'icon' => 'inbox'
    ],
    'sent' => [
        'label' => __('Sent'),
        'icon' => 'envelope'
    ],
    'trash' => [
        'label' => __('Trash'),
        'icon' => 'trash'
    ]
];
$currAction = $this->request->params['action'];
?>
<div class="message-folders">
    <p class="text-uppercase text-muted">
        Folder <a href="" class="pull-right"><i class="fa fa-refresh"></i></a>
    </p>
    <div class="list-group">
        <?php foreach ($actions as $action => $options) : ?>
            <?php
                if ('inbox' === $action) {
                    $cell = $this->cell('MessagingCenter.Inbox::unreadCount');
                    $options['label'] .= ' ' . $cell;
                }

                $options['icon'] = '<i class="fa fa-' .$options['icon'] . '"></i>';
            ?>
            <?= $this->Html->link(
                $options['icon'] . ' ' . $options['label'],
                ['action' => $action],
                ['escape' => false, 'class' => 'list-group-item' . ($action === $currAction ? ' disabled' : '')]
            ); ?>
        <?php endforeach; ?>
    </div>
</div>