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
    <table class="table table-hover table-condensed table-vertical-align table-datatable" width="100%">
        <thead>
            <tr>
                <th><?= __('Name') ?></th>
                <th><?= __('Type') ?></th>
                <th><?= __('Created') ?></th>
                <th><?= __('Action') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($mailbox->get('folders') as $folder) : ?>
            <tr>
                <td><?= $folder->get('name') ?></td>
                <td><?= $folder->get('type') ?></td>
                <td><?= $folder->get('created'); ?></td>
                <td><?= $this->Html->link('<i class="fa fa-eye"></i>', ['controller' => 'Folders', 'action' => 'view', $folder->get('id')], ['escape' => false, 'class' => 'btn btn-default', 'title' => __('View')]) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
