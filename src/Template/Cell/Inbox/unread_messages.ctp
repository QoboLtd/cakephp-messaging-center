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
?>
<!-- inner menu: contains the actual data -->
<ul class="menu">
    <?php foreach ($messages as $message) : ?>
        <li><!-- start message -->
            <a href="<?= $this->Url->build([
                'plugin' => 'MessagingCenter',
                'controller' => 'Messages',
                'action' => 'view',
                $message->id
            ]); ?>">
                <div class="pull-left">
                    <?= $this->element('MessagingCenter.Messages/avatar', [
                        'message' => $message,
                    ]) ?>
                </div>
                <h4>
                    <?= $this->element('MessagingCenter.Messages/from', [
                        'message' => $message,
                    ]) ?>
                    <small><i class="fa fa-clock-o"></i> <?= $this->Time->timeAgoInWords(h($message->date_sent->i18nFormat('yyyy-MM-dd HH:mm'))) ?></small>
                </h4>
                <p>
                    <?= $this->Text->truncate(
                        strip_tags($message->content),
                        (int)$contentLength,
                        ['ellipsis' => '...', 'exact' => false, 'html' => true]
                    ); ?>
                </p>
            </a>
        </li>
    <!-- end message -->
    <?php endforeach; ?>
</ul>
