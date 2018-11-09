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
namespace MessagingCenter\Notifier;

interface NotifierInterface
{
    /**
     * Sends notification.
     *
     * @return void
     */
    public function send(): void;

    /**
     * Validate notification data.
     *
     * @return void
     */
    public function validate(): void;

    /**
     * Message template setter.
     *
     * @param  string $template Template name
     * @return void
     */
    public function template(string $template): void;

    /**
     * Notification from user setter.
     *
     * @param  string $from From user
     * @return void
     */
    public function from(string $from): void;

    /**
     * Notification to user setter.
     *
     * @param  string $to To user
     * @return void
     */
    public function to(string $to): void;

    /**
     * Notification subject setter.
     *
     * @param  string $subject Subject
     * @return void
     */
    public function subject(string $subject): void;

    /**
     * Notification message setter.
     *
     * @param  string|array $message Message
     * @return void
     */
    public function message($message): void;
}
