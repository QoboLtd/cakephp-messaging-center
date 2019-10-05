<?php

namespace MessagingCenter\Message;

use PhpImap\IncomingMail;

class MailMessage implements MessageInterface
{
    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var callable
     */
    private $loadCallback;

    /**
     * @var ?\PhpImap\IncomingMail
     */
    private $incomingMail;

    /**
     * MailMessage constructor.
     * @param string $uniqueId Unique ID for this Message
     * @param callable $callback Callback that loads the Message from the IMAP Server
     */
    public function __construct(string $uniqueId, callable $callback)
    {
        $this->incomingMail = null;
        $this->uniqueId = trim($uniqueId);
        $this->loadCallback = $callback;
    }

    /**
     * @return string
     */
    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * @return \PhpImap\IncomingMail
     */
    public function getIncomingMail(): IncomingMail
    {
        if (empty($this->incomingMail)) {
            $this->incomingMail = call_user_func($this->loadCallback);

        }

        return $this->incomingMail;
    }
}
