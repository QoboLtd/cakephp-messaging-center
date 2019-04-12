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

use Cake\Core\Configure;
use Cake\ORM\Entity;

/**
 * Friendly display of the user in the to/from address
 * of the message.
 */

$displayUser = $user;
$systemUser = Configure::readOrFail('MessagingCenter.systemUser');

if ($user instanceof Entity) {
    $firstName = $user->get('first_name') ?? '';
    $lastName = $user->get('last_name') ?? '';
    $userName = $user->get('username') ?? '';

    $fullName = trim($firstName . ' ' . $lastName);
    $displayUser = $fullName ?? $userName;
}

if (is_string($user)) {
    if ($systemUser['id'] === $user) {
        $displayUser = $systemUser['name'];
    }
}

echo h($displayUser);
