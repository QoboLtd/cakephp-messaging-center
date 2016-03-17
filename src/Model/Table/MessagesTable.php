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
    const NEW_STATUS = 'new';
    const READ_STATUS = 'read';
    const ARCHIVED_STATUS = 'archived';
    const DELETED_STATUS = 'deleted';
    const STARRED_STATUS = 'starred';

    const INBOX_FOLDER = 'inbox';
    const ARCHIVED_FOLDER = 'archived';
    const SENT_FOLDER = 'sent';
    const TRASH_FOLDER = 'trash';

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
        return static::NEW_STATUS;
    }

    /**
     * Returns string to be used as status field value when a message is read.
     * @return string
     */
    public function getReadStatus()
    {
        return static::READ_STATUS;
    }

    /**
     * Returns string to be used as status field value when a message is deleted.
     * @return string
     */
    public function getDeletedStatus()
    {
        return static::DELETED_STATUS;
    }

    /**
     * Returns string to be used as status field value when a message is archived.
     * @return string
     */
    public function getArchivedStatus()
    {
        return static::ARCHIVED_STATUS;
    }

    /**
     * Returns sent folder name.
     * @return string
     */
    public function getSentFolder()
    {
        return static::SENT_FOLDER;
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
     * Get message's folder based on user id and message status.
     * @param  \MessagingCenter\Model\Entity\Message $message Message enity
     * @param  string $userId current user id
     * @return string         folder name
     */
    public function getFolderByMessage(Message $message, $userId)
    {
        if ($message->from_user !== $userId) {
            switch ($message->status) {
                case static::DELETED_STATUS:
                    $result = 'trash';
                    break;

                case static::ARCHIVED_STATUS:
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
     * @param  string $type   folder type
     * @return array          query conditions
     */
    public function getConditionsByFolderType($userId, $type = '')
    {
        switch ($type) {
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
