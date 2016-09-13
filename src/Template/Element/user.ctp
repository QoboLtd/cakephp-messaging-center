<?php
/**
 * Friendly display of the user in the to/from address
 * of the message.
 */
$displayUser = __('Anonymous');
if (!empty($user)) {
    $firstName = isset($user->first_name) ? $user->first_name : '';
    $lastName = isset($user->last_name) ? $user->last_name : '';
    $userName = isset($user->username) ? $user->username : '';

    $fullName = trim($firstName . ' ' . $lastName);
    if (!empty($fullName)) {
        $displayUser = $fullName;
    } else {
        $displayUser = $userName;
    }
}

echo $displayUser;
