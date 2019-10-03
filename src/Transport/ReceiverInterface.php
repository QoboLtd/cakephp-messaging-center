<?php

namespace MessagingCenter\Transport;

interface ReceiverInterface
{
    /**
     * @return \MessagingCenter\Model\Entity\Message[]
     */
    public function recieve(): array;
}
