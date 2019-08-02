<?php

use Cake\Core\Configure;
use Cake\ORM\Entity;

$user = $message->toUser ?? $message->get('to_user');
$systemUser = Configure::readOrFail('MessagingCenter.systemUser');

if ($user instanceof Entity) {
    $firstName = $user->get('first_name') ?? '';
    $lastName = $user->get('last_name') ?? '';
    $userName = $user->get('username') ?? '';

    $fullName = trim($firstName . ' ' . $lastName);
    $displayUser = $fullName ?: $userName;

    echo h($displayUser);

    return;
}

if (is_string($user) && $systemUser['id'] === $user) {
    echo h($systemUser['name']);

    return;
}

if ($message->get('headers')) {
    $headers = $message->get('headers');
    if (!empty($headers['toaddress'])) {
        echo h($headers['toaddress']);

        return;
    }
}

echo h($user);
