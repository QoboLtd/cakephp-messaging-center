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
