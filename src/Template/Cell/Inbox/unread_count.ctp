<?php
if (0 < $unread_count) {
    if ($max_unread_count < $unread_count) {
        $unread_count = $max_unread_count . '+';
    }
    echo str_replace('{{text}}', $unread_count, $unread_format);
}
?>