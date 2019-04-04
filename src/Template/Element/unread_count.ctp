<?php
$unreadCount = (int)$this->cell('MessagingCenter.Inbox::unreadCount', ['{{text}}', $mailbox])->render();
?>
<small><?= $unreadCount > 0 ? $unreadCount . ' ' . __('new messages') : '' ?></small>
