<?php
$unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}', $mailbox])->render();
?>
<small><?= $unreadCount > 0 ? $unreadCount . ' ' . __d('Qobo/MessagingCenter', 'new messages') : '' ?></small>
