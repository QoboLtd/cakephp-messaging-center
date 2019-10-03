<?php

namespace MessagingCenter\Transport;

interface StoreInterface
{
    /**
     * @return \MessagingCenter\Model\Entity\Message[]
     */
    public function getMessages(): array;
}
