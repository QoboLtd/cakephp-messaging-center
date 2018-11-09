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
        'icon' => 'envelope-o'
    ],
    'trash' => [
        'label' => __('Trash'),
        'icon' => 'trash-o'
    ]
];

if (!isset($folder)) {
    $folder = $this->request->getParam('pass.0') ? $this->request->getParam('pass.0') : 'inbox';
}
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Folders</h3>
        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
        </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked">
        <?php foreach ($actions as $action => $options) : ?>
            <li class="<?= $action === $folder ? ' active' : ''; ?>">
            <?php
            if ('inbox' === $action) {
                $unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}'])->render();
                if (0 < $unreadCount) {
                    $options['label'] .= ' <span class="label label-primary pull-right">' . $unreadCount . '</span>';
                }
            }

                $options['icon'] = '<i class="fa fa-' . $options['icon'] . '"></i>';
            ?>
            <?= $this->Html->link(
                $options['icon'] . ' ' . $options['label'],
                ['plugin' => 'MessagingCenter', 'controller' => 'Messages', 'action' => 'folder', $action],
                ['escape' => false]
            ); ?>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
