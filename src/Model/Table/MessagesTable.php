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
namespace MessagingCenter\Model\Table;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use InvalidArgumentException;
use MessagingCenter\Model\Entity\Folder;
use Webmozart\Assert\Assert;

/**
 * Messages Model
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 */
class MessagesTable extends Table
{
    const STATUS_NEW = 'new';
    const STATUS_READ = 'read';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_DELETED = 'deleted';
    const STATUS_STARRED = 'starred';

    const FOLDER_INBOX = 'Inbox';
    const FOLDER_ARCHIVED = 'Archived';
    const FOLDER_SENT = 'Sent';
    const FOLDER_TRASH = 'Trash';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('qobo_messages');
        $this->setDisplayField('subject');
        $this->setPrimaryKey('id');

        $this->belongsTo('Folders', [
            'foreignKey' => 'folder_id',
            'joinType' => 'INNER',
            'className' => 'MessagingCenter.Folders'
        ]);

        $this->belongsTo('FromUser', [
            'foreignKey' => 'from_user',
            'className' => 'CakeDC/Users.Users',
            'propertyName' => 'fromUser'
        ]);

        $this->belongsTo('ToUser', [
            'foreignKey' => 'to_user',
            'className' => 'CakeDC/Users.Users',
            'propertyName' => 'toUser'
        ]);

        $this->addBehavior('Timestamp');
    }

    /**
     * @inheritDoc
     *
     * @param \Cake\Database\Schema\TableSchema $schema Schema to be initialized
     * @return \Cake\Database\Schema\TableSchema
     */
    protected function _initializeSchema(TableSchema $schema)
    {
        $schema = parent::_initializeSchema($schema);

        $schema->setColumnType('headers', 'json');

        return $schema;
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->uuid('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('from_user', 'create')
            ->allowEmpty('from_user');

        $validator
            ->requirePresence('to_user', 'create')
            ->allowEmpty('to_user');

        $validator
            ->requirePresence('subject', 'create')
            ->allowEmpty('subject');

        $validator
            ->requirePresence('content', 'create')
            ->notEmpty('content');

        $validator
            ->dateTime('date_sent')
            ->allowEmpty('date_sent');

        $validator
            ->requirePresence('status', 'create')
            ->notEmpty('status');

        $validator
            ->allowEmpty('related_model');

        $validator
            ->allowEmpty('related_id');

        return $validator;
    }

    /**
     * Returns string to be used as status field value when a new message is created.
     *
     * @return string
     */
    public function getNewStatus(): string
    {
        return static::STATUS_NEW;
    }

    /**
     * Returns string to be used as status field value when a message is read.
     *
     * @return string
     */
    public function getReadStatus(): string
    {
        return static::STATUS_READ;
    }

    /**
     * Returns string to be used as status field value when a message is deleted.
     *
     * @return string
     */
    public function getDeletedStatus(): string
    {
        return static::STATUS_DELETED;
    }

    /**
     * Returns string to be used as status field value when a message is archived.
     *
     * @return string
     */
    public function getArchivedStatus(): string
    {
        return static::STATUS_ARCHIVED;
    }

    /**
     * Returns sent folder name.
     *
     * @return string
     */
    public function getSentFolder(): string
    {
        return static::FOLDER_SENT;
    }

    /**
     * Returns Time object to be used as date_sent field value.
     *
     * @return \Cake\I18n\Time
     */
    public function getDateSent(): \Cake\I18n\Time
    {
        $result = new Time();

        return $result;
    }

    /**
     * Method that returns default folder's name.
     *
     * @return string
     */
    public function getDefaultFolder(): string
    {
        return static::FOLDER_INBOX;
    }

    /**
     * Method that returns all folder names.
     *
     * @return string[]
     */
    public function getFolders(): array
    {
        $result = [
            static::FOLDER_INBOX,
            static::FOLDER_ARCHIVED,
            static::FOLDER_SENT,
            static::FOLDER_TRASH
        ];

        return $result;
    }

    /**
     * Check if folder exists.
     * @param  string $folder folder name
     * @return bool
     */
    public function folderExists(string $folder = ''): bool
    {
        if (!in_array($folder, $this->getFolders())) {
            return false;
        }

        return true;
    }

    /**
     * Get message's folder based on http referer, if not
     * matched get it from user id and message status.
     * @param \Cake\Datasource\EntityInterface $message Message enity
     * @param string $userId current user id
     * @return \Cake\Datasource\EntityInterface folder
     */
    public function getFolderByMessage(EntityInterface $message, string $userId) : EntityInterface
    {
        $table = TableRegistry::getTableLocator()->get('MessagingCenter.Folders');
        Assert::isInstanceOf($table, FoldersTable::class);

        $folder = $table->get($message->get('folder_id'));
        Assert::isInstanceOf($folder, Folder::class);

        return $folder;
    }

    /**
     * Get query conditions based on folder type.
     * @param  string $userId current user id
     * @param  string $folder folder
     * @return mixed[]          query conditions
     */
    public function getConditionsByFolder(string $userId, string $folder = ''): array
    {
        switch ($folder) {
            case 'archived':
                $result = [
                    'to_user' => $userId,
                    'status' => $this->getArchivedStatus()
                ];
                break;

            case 'sent':
                $result = ['from_user' => $userId];
                break;

            case 'trash':
                $result = [
                    'to_user' => $userId,
                    'status' => $this->getDeletedStatus()
                ];
                break;

            case 'inbox':
            default:
                $result = [
                    'to_user' => $userId,
                    'status IN' => [$this->getReadStatus(), $this->getNewStatus()]
                ];
                break;
        }

        return $result;
    }

    /**
     * @param \MessagingCenter\Model\Entity\Folder[] $folders List of folders to be checked
     * @param string $name Folder name that we are looking for
     * @return Folder
     */
    private function getFolderByType(array $folders, string $name): Folder
    {
        foreach ($folders as $folder) {
            if ($folder->get('name') === $name) {
                return $folder;
            }
        }

        throw new InvalidArgumentException(sprintf('Folder with name %s not found', $name));
    }

    /**
     * processMessages method
     *
     * @param string $userId who own the messages
     * @param mixed[] $folders to move message
     * @return bool
     */
    public function processMessages(string $userId, array $folders) : bool
    {
        $query = $this->find()
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

            $folder = $this->getFolderByType($folders, MailboxesTable::FOLDER_INBOX);
            $copiedMessageUser = $message->get('from_user');
            $copiedMessageFolder = MailboxesTable::FOLDER_SENT;

            if ($message->get('from_user') == $userId) {
                $folder = $this->getFolderByType($folders, MailboxesTable::FOLDER_SENT);
                $copiedMessageUser = $message->get('to_user');
                $copiedMessageFolder = MailboxesTable::FOLDER_INBOX;
            }

            $this->patchEntity($message, [
                'folder_id' => $folder->get('id')
            ]);

            $this->save($message);

            if ($copiedMessageUser != (string)Configure::read('MessagingCenter.systemUser.id')) {
                $this->copyMessage($message->toArray(), $copiedMessageUser, $copiedMessageFolder);
            }
        }

        return true;
    }

    /**
     * copyMessage method
     *
     * @param mixed[] $data to copy
     * @param string $userId to copy message
     * @param string $folderType to copy message
     * @return bool
     */
    protected function copyMessage(array $data, string $userId, string $folderType) : bool
    {
        unset($data['id']);

        $userTable = TableRegistry::getTableLocator()->get('Users');
        Assert::isInstanceOf($userTable, Table::class);

        $user = $userTable->get($userId);
        Assert::isInstanceOf($user, EntityInterface::class);

        $mailboxesTable = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxesTable, MailboxesTable::class);

        $mailbox = $mailboxesTable->createDefaultMailbox($user->toArray());
        Assert::isInstanceOf($mailbox, EntityInterface::class);

        $foldersTable = TableRegistry::getTableLocator()->get('MessagingCenter.Folders');
        Assert::isInstanceOf($foldersTable, FoldersTable::class);

        $folders = $foldersTable->createDefaultFolders($mailbox);

        if (empty($folders)) {
            return false;
        }

        $folder = $this->getFolderByType($folders, $folderType);
        $data['folder_id'] = $folder->get('id');

        $entity = $this->newEntity();
        $this->patchEntity($entity, $data);
        $result = $this->save($entity);

        return !empty($result) ? true : false;
    }

    /**
     * {@inheritDoc}
     * @return void|bool
     */
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        $content = (string)$entity->get('content');
        /** @see https://codex.wordpress.org/Function_Reference/wp_strip_all_tags */
        $content = (string)preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $content);

        $entity->set('content', $content);
    }
}
