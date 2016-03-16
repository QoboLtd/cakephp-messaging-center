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

        $this->belongsTo('Users', [
            'foreignKey' => 'from_user',
            'className' => 'CakeDC/Users.Users'
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
    public function getNewMessageStatus()
    {
        return static::NEW_STATUS;
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
}
