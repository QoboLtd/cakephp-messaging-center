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
namespace MessagingCenter\Event\Model;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use MessagingCenter\Event\EventName;
use MessagingCenter\Model\Table\FoldersTable;
use MessagingCenter\Model\Table\MessagesTable;
use Webmozart\Assert\Assert;

class MailboxListener implements EventListenerInterface
{
    const MAILBOX_TABLE_NAME = 'MessagingCenter.Mailboxes';
    const FOLDERS_TABLE_NAME = 'MessagingCenter.Folders';
    const MESSAGES_TABLE_NAME = 'MessagingCenter.Messages';

    /**
     * implementedEvents method
     *
     * @return mixed[]
     */
    public function implementedEvents(): array
    {
        return [
            (string)EventName::CAKE_ORM_MODEL_AFTER_SAFE() => [
                'callable' => 'createFolders',
                'priority' => 8,
            ],
        ];
    }

    /**
     * createFolders method
     *
     * @param \Cake\Event\Event $event event.
     * @param \Cake\Datasource\EntityInterface $entity entity.
     * @param \ArrayObject $options options.
     * @return void
     */
    public function createFolders(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        if (!$entity->isNew() || $entity->getSource() != self::MAILBOX_TABLE_NAME) {
            return;
        }

        $foldersTable = TableRegistry::getTableLocator()->get(self::FOLDERS_TABLE_NAME);
        Assert::isInstanceOf($foldersTable, FoldersTable::class);
        $list = $foldersTable->createDefaultFolders($entity);

        if (!empty($list)) {
            $this->processMessages($entity->get('user_id'), $list);
        }
    }

    /**
     * processMessages method
     *
     * @param string $userId who own the messages
     * @param mixed[] $folders to move message
     * @return bool
     */
    protected function processMessages(string $userId, array $folders): bool
    {
        $messagesTable = TableRegistry::getTableLocator()->get(self::MESSAGES_TABLE_NAME);
        Assert::isInstanceOf($messagesTable, MessagesTable::class);

        return $messagesTable->processMessages($userId, $folders);
    }
}
