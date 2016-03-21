<?php
namespace MessagingCenter\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use MessagingCenter\Model\Entity\Message;

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

    const FOLDER_INBOX = 'inbox';
    const FOLDER_ARCHIVED = 'archived';
    const FOLDER_SENT = 'sent';
    const FOLDER_TRASH = 'trash';

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('messages');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

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
            ->uuid('from_user')
            ->requirePresence('from_user', 'create')
            ->notEmpty('from_user');

        $validator
            ->uuid('to_user')
            ->requirePresence('to_user', 'create')
            ->notEmpty('to_user');

        $validator
            ->requirePresence('subject', 'create')
            ->notEmpty('subject');

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

        return $validator;
    }

    /**
     * Returns string to be used as status field value when a new message is created.
     * @return string
     */
    public function getNewStatus()
    {
        return static::STATUS_NEW;
    }

    /**
     * Returns string to be used as status field value when a message is read.
     * @return string
     */
    public function getReadStatus()
    {
        return static::STATUS_READ;
    }

    /**
     * Returns string to be used as status field value when a message is deleted.
     * @return string
     */
    public function getDeletedStatus()
    {
        return static::STATUS_DELETED;
    }

    /**
     * Returns string to be used as status field value when a message is archived.
     * @return string
     */
    public function getArchivedStatus()
    {
        return static::STATUS_ARCHIVED;
    }

    /**
     * Returns sent folder name.
     * @return string
     */
    public function getSentFolder()
    {
        return static::FOLDER_SENT;
    }

    /**
     * Returns Time object to be used as date_sent field value.
     * @return Cake\I18n\Time
     */
    public function getDateSent()
    {
        $result = new Time();

        return $result;
    }

    /**
     * Method that returns default folder's name.
     * @return string
     */
    public function getDefaultFolder()
    {
        return static::FOLDER_INBOX;
    }

    /**
     * Method that returns all folder names.
     * @return array
     */
    public function getFolders()
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
    public function folderExists($folder = '')
    {
        if (!in_array($folder, $this->getFolders())) {
            return false;
        }

        return true;
    }

    /**
     * Get message's folder based on http referer, if not
     * matched get it from user id and message status.
     * @param  \MessagingCenter\Model\Entity\Message $message Message enity
     * @param  string $userId current user id
     * @param  string $referer http referer
     * @return string         folder name
     */
    public function getFolderByMessage(Message $message, $userId, $referer = '')
    {
        $result = substr($referer, strrpos($referer, '/') + 1);

        if (in_array($result, $this->getFolders())) {
            return $result;
        }

        if ($message->from_user !== $userId) {
            switch ($message->status) {
                case static::STATUS_DELETED:
                    $result = 'trash';
                    break;

                case static::STATUS_ARCHIVED:
                    $result = 'archived';
                    break;

                default:
                    $result = 'inbox';
                    break;
            }
        } else {
            $result = 'sent';
        }

        return $result;
    }

    /**
     * Get query conditions based on folder type.
     * @param  string $userId current user id
     * @param  string $folder folder
     * @return array          query conditions
     */
    public function getConditionsByFolder($userId, $folder = '')
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
}
