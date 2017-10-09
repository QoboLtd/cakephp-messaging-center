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
     * Constructor method.
     */
    public function __construct();

    /**
     * Sends notification.
     *
     * @return void
     */
    public function send();

    /**
     * Validate notification data.
     *
     * @throws InvalidArgumentException
     * @return void
     */
    public function validate();

    /**
     * Message template setter.
     *
     * @param  string $template Template name
     * @throws InvalidArgumentException
     * @return void
     */
    public function template($template);

    /**
     * Notification from user setter.
     *
     * @param  string $from From user
     * @return void
     */
    public function from($from);

    /**
     * Notification to user setter.
     *
     * @param  string $to To user
     * @return void
     */
    public function to($to);

    /**
     * Notification subject setter.
     *
     * @param  string $subject Subject
     * @return void
     */
    public function subject($subject);

    /**
     * Notification message setter.
     *
     * @param  string $message Message
     * @return void
     */
    public function message($message);
}
