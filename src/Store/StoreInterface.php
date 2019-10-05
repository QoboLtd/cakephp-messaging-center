<?php

namespace MessagingCenter\Store;

interface StoreInterface
{
    /**
     * @param int|null $limit How many messages to return
     * @param int $offset How many rows to skip before beginning to return messages
     * @return \MessagingCenter\Model\Entity\Message[]
     */
    public function getMessages(int $limit = null, int $offset = 0): array;

    /**
     * @param string[] $criteria Search criteria
     * @param int|null $limit How many messages to return
     * @param int $offset How many rows to skip before beginning to return messages
     * @return \MessagingCenter\Model\Entity\Message[]
     */
    public function searchMessages(array $criteria, int $limit = null, int $offset = 0): array;
}
