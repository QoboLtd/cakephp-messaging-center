<?php
use Cake\Core\Configure;
use Cake\ORM\Entity;
/**
 * Friendly display of the user in the to/from address
 * of the message.
 */
$displayUser = __('Anonymous');
$systemUser = Configure::readOrFail('MessagingCenter.systemUser');

if ($user instanceof Entity) {
    $firstName = isset($user->first_name) ? $user->first_name : '';
    $lastName = isset($user->last_name) ? $user->last_name : '';
    $userName = isset($user->username) ? $user->username : '';

    $fullName = trim($firstName . ' ' . $lastName);
    if (!empty($fullName)) {
        $displayUser = $fullName;
    } else {
        $displayUser = $userName;
    }
} elseif (is_string($user)) {
    if ($systemUser['id'] === $user) {
        $displayUser = $systemUser['name'];
    }
}

echo h($displayUser);
