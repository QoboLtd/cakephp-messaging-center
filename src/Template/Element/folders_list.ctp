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

use MessagingCenter\Model\Table\MailboxesTable;

if (!isset($folderName)) {
    $folderName = MailboxesTable::FOLDER_INBOX;
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
        <?php foreach ($mailbox->get('folders') as $folder) : ?>
            <li class="<?= $folder->get('name') === $folderName ? ' active' : ''; ?>">
            <?php
                $label = $folder->get('name');
                if (MailboxesTable::FOLDER_INBOX === $folder->get('name')) {
                    $unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}', $mailbox])->render();
                    if (0 < $unreadCount) {
                        $label .= ' <span class="label label-primary pull-right">' . $unreadCount . '</span>';
                    }
                }

                $icon = '<i class="fa fa-' . $folder->get('icon') . '"></i>';
            ?>
            <?= $this->Html->link($icon . ' ' . $label, [
                'plugin' => 'MessagingCenter',
                'controller' => 'Mailboxes',
                'action' => 'view',
                $mailbox->get('id'),
                '?' => ['folder_id' => $folder->get('id')]
                ],
                ['escape' => false]
            ); ?>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
</div>
