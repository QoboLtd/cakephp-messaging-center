<?php
if (0 < $unreadCount) {
    if ($maxUnreadCount < $unreadCount) {
        $unreadCount = $maxUnreadCount . '+';
    }
    echo str_replace('{{text}}', $unreadCount, $unreadFormat);
}
