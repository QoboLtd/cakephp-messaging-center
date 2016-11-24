<?php
$actions = [
    'inbox' => [
        'label' => __('Inbox'),
        'icon' => 'inbox'
    ],
    'archived' => [
        'label' => __('Archived'),
        'icon' => 'archive'
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

if (!isset($folder)) {
    $folder = isset($this->request->params['pass'][0]) ? $this->request->params['pass'][0] : 'inbox';
}
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
                ['action' => 'folder', $action],
                ['escape' => false, 'class' => 'list-group-item' . ($action === $folder ? ' disabled' : '')]
            ); ?>
        <?php endforeach; ?>
    </div>
</div>