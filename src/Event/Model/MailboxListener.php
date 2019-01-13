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

use App\Model\Table\MailboxesTable;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\ORM\TableRegistry;
use MessagingCenter\Event\EventName;

class MailboxListener implements EventListenerInterface
{
    const MAILBOX_TABLE_NAME = 'QoboMailboxes';
    const FOLDERS_TABLE_NAME = 'QoboFolders';
    const MESSAGES_TABLE_NAME = 'QoboMessages';
    const USERS_TABLE_NAME = 'Users';

    /**
     * implementedEvents method
     *
     * @return mixed[]
     */
    public function implementedEvents() : array
    {
        return [
            (string)EventName::CAKE_ORM_MODEL_AFTER_SAFE => 'createFolders',
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
    public function createFolders(Event $event, EntityInterface $entity, ArrayObject $options) : void
    {
        if (!$entity->isNew()) {
            return;
        }

        if ($entity->getSource() == self::MAILBOX_TABLE_NAME) {
            return;
        }

        $list = [];
        $foldersTable = TableRegistry::getTableLocator()->get(self::FOLDERS_TABLE_NAME);
        foreach (MailboxesTable::getDefaultFolders() as $folderName) {
            $query = $foldersTable->find()
                ->where([
                    'name' => $folderName,
                    'mailbox_id' => $entity->get('id')
                ]);

            $result = $query->first();

            if (empty($result)) {
                $folder = $foldersTable->newEntity();
                $foldersTable->patchEntity($folder, [
                    'mailbox_id' => $entity->get('id'),
                    'name' => $folderName,
                ]);

                $result = $foldersTable->save($folder);
            }

            $list[$folderName] = $result;
        }

        if (!empty($list)) {
            $this->processMessages();
        }
    }

    /**
     * processMessages method
     *
     * @param string $userId who own the messages
     * @param mixed[] $folders to move message
     */
    protected function processMessages(string $userId, array $folders) : void
    {
        $messagesTable = TableRegistry::getTableLocator()->get(self::MESSAGES_TABLE_NAME);
        $query = $messagesTable->find()
            ->where([
                'OR' => [
                    'from_user' => $userId,
                    'to_user' => $userId,
                ]
            ]);
        $query->execute();

        foreach ($query->all() as $message) {
            if (!empty($message->get('folder_id'))) {
                continue;
            }

            $folder = $folders[MailboxesTable::FOLDER_INBOX];
            if ($message->get('from_user') == $userId) {
                $folder = $folders[MailboxesTable::FOLDER_SENT];
                if ($message->get('to_user') != self::SYSTEM_USER_ID) {
                    $this->copyMessage($message->toArray(), $message->get('from_user'));
                }
            }

            $messagesTable->patchEntity($message, [
                'folder_id' => $folder->get('id')
            ]);

            $messagesTable->save($message);
        }
    }

    /**
     * copyMessage method
     *
     * @param mixed[] $data to copy
     * @param string $userId to copy message
     * @return bool
     */
    protected function copyMessage(array $data, string $userId) : bool
    {
        unset($data['id']);

        $userTable = TableRegistry::getTableLocator()->get(self::USERS_TABLE_NAME);
        $user = $userTable->get($userId);

        if (empty($user)) {
            return false;
        }

        $mailbox = $this->createMailbox($user);
        if (empty($mailbox)) {
            return false;
        }

        $folders = $this->createFolders($mailbox);
        if (empty($folders)) {
            return false;
        }

        $data['folder_id'] = $folders[MailboxesTable::FOLDER_SENT];

        $table = TableRegistry::getTableLocator()->get(self::MESSAGES_TABLE_NAME);
        $entity = $table->newEntity();
        $table->patchEntity($entity, $data);
        $result = $table->save($entity);

        return !empty($result) ? true : false;
    }
}
