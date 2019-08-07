<?php
namespace MessagingCenter\Model\Table;

use Cake\Core\Configure;
use Cake\Database\Schema\TableSchema;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\QueryInterface;
use Cake\Datasource\ResultSetInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;
use InvalidArgumentException;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Entity\Folder;
use MessagingCenter\Model\Entity\Mailbox;
use Webmozart\Assert\Assert;

/**
 * Mailboxes Model
 *
 * @property \MessagingCenter\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \MessagingCenter\Model\Entity\Mailbox get($primaryKey, $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox newEntity($data = null, array $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox[] newEntities(array $data, array $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox[] patchEntities($entities, array $data, array $options = [])
 * @method \MessagingCenter\Model\Entity\Mailbox findOrCreate($search, callable $callback = null, $options = [])
 * @method \Cake\ORM\Query findAllByTypeAndActive(string $type, bool $active))
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MailboxesTable extends Table
{
    const FOLDER_INBOX = 'Inbox';
    const FOLDER_SENT = 'Sent';
    const FOLDER_ARCHIVE = 'Archive';
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

        $this->setTable('qobo_mailboxes');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
            'className' => 'CakeDC/Users.Users'
        ]);

        $this->hasMany('Folders', [
            'className' => 'MessagingCenter.Folders',
            'foreignKey' => 'mailbox_id'
        ]);
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
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
            ->scalar('type')
            ->maxLength('type', 255)
            ->requirePresence('type', 'create')
            ->notEmpty('type');

        $validator
            ->scalar('incoming_transport')
            ->maxLength('incoming_transport', 255)
            ->requirePresence('incoming_transport', 'create')
            ->notEmpty('incoming_transport');

        $validator
            ->scalar('incoming_settings')
            ->maxLength('incoming_settings', 4294967295)
            ->requirePresence('incoming_settings', 'create')
            ->notEmpty('incoming_settings');

        $validator
            ->scalar('outgoing_transport')
            ->maxLength('outgoing_transport', 255)
            ->requirePresence('outgoing_transport', 'create')
            ->notEmpty('outgoing_transport');

        $validator
            ->scalar('outgoing_settings')
            ->maxLength('outgoing_settings', 4294967295)
            ->requirePresence('outgoing_settings', 'create')
            ->notEmpty('outgoing_settings');

        $validator
            ->boolean('active')
            ->requirePresence('active', 'create')
            ->notEmpty('active');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
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

        $schema->setColumnType('incoming_settings', 'json');
        $schema->setColumnType('outgoing_settings', 'json');

        return $schema;
    }

    /**
     * getDefaultFolders method
     *
     * @return mixed[]
     */
    public static function getDefaultFolders() : array
    {
        return [
            self::FOLDER_INBOX,
            self::FOLDER_SENT,
            self::FOLDER_ARCHIVE,
            self::FOLDER_TRASH,
        ];
    }

    /**
     * createDefaultMailbox method
     *
     * @param mixed[] $user to create a mailbox for
     * @return \Cake\Datasource\EntityInterface
     * @throws \InvalidArgumentException in case of no mailbox is created
     */
    public function createDefaultMailbox(array $user) : EntityInterface
    {
        $options = (array)Configure::read('MessagingCenter.Mailbox.default');

        $mailboxName = $user['username'] . $options['mailbox_postfix'];

        $query = $this->find()
            ->enableHydration(true)
            ->where([
                'name' => $mailboxName,
                'user_id' => $user['id']
            ]);

        $result = $query->first();
        if ($result instanceof EntityInterface) {
            return $result;
        }

        $mailbox = $this->newEntity();
        $this->patchEntity($mailbox, [
            'name' => $mailboxName,
            'user_id' => $user['id'],
            'type' => $options['mailbox_type'],
            'incoming_transport' => $options['incoming_transport'],
            'incoming_settings' => $options['incoming_settings'],
            'outgoing_transport' => $options['outgoing_transport'],
            'outgoing_settings' => $options['outgoing_settings'],
            'active' => true
        ]);
        $result = $this->save($mailbox);

        if (empty($result) || ! $result instanceof EntityInterface) {
            throw new InvalidArgumentException('Cannot create mailbox for user [' . $user['username'] .
                        ']: please check input parameters [' . json_encode($mailbox->getErrors()) . ']');
        }

        return $result;
    }

    /**
     * Returns the inbox folder for this Mailbox.
     *
     * The folder ID is returned.
     *
     * @param \Cake\Datasource\EntityInterface $mailbox to get default folder for
     * @return string
     * @throws InvalidArgumentException in case of no Inbox folder found in the mailbox
     */
    public function getInboxFolder(EntityInterface $mailbox) : string
    {
        $foldersTable = TableRegistry::getTableLocator()->get('MessagingCenter.Folders');
        $query = $foldersTable
            ->find()
            ->where([
                'mailbox_id' => (string)$mailbox->get('id'),
                'name' => static::FOLDER_INBOX,
            ]);
        Assert::isInstanceOf($query, QueryInterface::class);

        $folder = $query->firstOrFail();
        Assert::isInstanceOf($folder, Folder::class);

        return $folder->get('id');
    }

    /**
     * Returns all the folders available under the specified Mailbox
     *
     * @param \Cake\Datasource\EntityInterface $mailbox to get folders for
     * @return \Cake\Datasource\EntityInterface[]
     * @throws InvalidArgumentException in case of no Inbox folder found in the mailbox
     */
    public function getFolders(EntityInterface $mailbox) : array
    {
        $query = $this->find()
            ->where([
                'id' => (string)$mailbox->get('id'),
            ])
            ->contain(['Folders']);
        Assert::isInstanceOf($query, QueryInterface::class);

        if ($query->isEmpty()) {
            throw new InvalidArgumentException('Cannot find folders in that mailbox');
        }

        $mailbox = $query->firstOrFail();
        Assert::isInstanceOf($mailbox, Mailbox::class);

        return $mailbox->get('folders');
    }

    /**
     * getSystemMailbox method
     *
     * @param mixed[] $user to find system mailbox
     * @return \Cake\Datasource\EntityInterface
     */
    public function getSystemMailbox(array $user) : EntityInterface
    {
        $query = $this->find()
            ->enableHydration(true)
            ->where([
                'user_id' => $user['id']
            ]);
        $mailbox = $query->first();
        Assert::isInstanceOf($mailbox, EntityInterface::class, __('User ' . $user['username'] . ' does not have system mailbox!'));

        return $mailbox;
    }

    /**
     * Counts and returns the number of unread messages within the provided mailbox.
     *
     * @param \MessagingCenter\Model\Entity\Mailbox $mailbox Mailbox entity
     * @return int
     */
    public function countUnreadMessages(Mailbox $mailbox): int
    {
        return (int)$this->queryUnreadMessages($mailbox)->count();
    }

    /**
     * Fetches and returns the unread messages within the provided mailbox.
     *
     * @param \MessagingCenter\Model\Entity\Mailbox $mailbox Mailbox entity
     * @param int|null $limit Number of records to be fetched
     * @return \MessagingCenter\Model\Entity\Message[]
     */
    public function getUnreadMessages(Mailbox $mailbox, int $limit = null): array
    {
        $query = $this
            ->queryUnreadMessages($mailbox)
            ->contain(['FromUser', 'ToUser']);
        Assert::isInstanceOf($query, Query::class);

        $query->order(['Messages.date_sent' => 'DESC']);
        if (is_int($limit)) {
            $query->limit($limit);
        }

        return $query->toList();
    }

    /**
     * Prepares and returns the query for Unread Messages
     *
     * @param \MessagingCenter\Model\Entity\Mailbox $mailbox Mailbox Enitty
     * @return \Cake\ORM\Query
     */
    protected function queryUnreadMessages(Mailbox $mailbox): Query
    {
        $mailboxId = $mailbox->get('id');

        $messagesTable = TableRegistry::getTableLocator()->get('MessagingCenter.Messages');
        Assert::isInstanceOf($messagesTable, MessagesTable::class);

        $query = $messagesTable->find('all')
            ->where([
                'status' => $messagesTable->getNewStatus(),
            ])
            ->contain([
                'Folders' => function (Query $q) use ($mailboxId) {
                    return $q->where([
                        'mailbox_id' => $mailboxId,
                        'name' => MailboxesTable::FOLDER_INBOX,
                    ]);
                }
            ]);
        Assert::isInstanceOf($query, Query::class);

        return $query;
    }

    /**
     * Returns all the active mailboxes.
     *
     * When the type is provided, only active mailboxes of the specified type are being returned
     *
     * @param null|string $type Mailbox type
     * @return \Cake\Datasource\ResultSetInterface
     */
    public function getActiveMailboxes(?string $type = null): ResultSetInterface
    {
        $query = $this->find()->contain(['Folders']);
        Assert::isInstanceOf($query, Query::class);

        if (empty($type)) {
            return $query->where(['active' => true])->all();
        }

        return $query
            ->where([
                'type' => $type,
                'active' => true,
            ])
            ->all();
    }
}
