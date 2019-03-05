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
 * IncomingTransportType Enum
 */
class IncomingTransportType extends Enum
{
    /**
     * Internal system messages
     */
    const INTERNAL = 'internal';

    /**
     * IMAP4 emails
     */
    const IMAP4 = 'imap4';
}
