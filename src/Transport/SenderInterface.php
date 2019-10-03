<?php

namespace MessagingCenter\Transport;

use MessagingCenter\Model\Entity\Message;

interface SenderInterface
{
    /**
     * @param \MessagingCenter\Model\Entity\Message $message Message to be sent
     */
    public function send(Message $message): void;
}
