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
namespace MessagingCenter\Enum;

use MyCLabs\Enum\Enum;

/**
 * MailboxType Enum
 */
class MailboxType extends Enum
{
    /**
     * Internal system messages
     */
    const SYSTEM = 'system';

    /**
     * Emails
     */
    const EMAIL = 'email';
}
