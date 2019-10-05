<?php

namespace MessagingCenter\Message;

interface MessageInterface
{
    /**
     * @return string
     */
    public function getUniqueId(): string;
}
