<?php
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
