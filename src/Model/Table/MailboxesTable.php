<?php
namespace MessagingCenter\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

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
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class MailboxesTable extends Table
{

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
            'className' => 'MessagingCenter.Users'
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
}