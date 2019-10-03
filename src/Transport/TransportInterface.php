<?php

namespace MessagingCenter\Transport;

use MessagingCenter\Model\Entity\Message;

interface TransportInterface
{
    /**
     * @param \MessagingCenter\Model\Entity\Message $message Message to be sent
     */
    public function sendMessage(Message $message): void;
}
