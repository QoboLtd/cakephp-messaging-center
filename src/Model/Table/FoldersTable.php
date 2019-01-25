<?php
namespace MessagingCenter\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use InvalidArgumentException;

/**
 * Folders Model
 *
 * @property \MessagingCenter\Model\Table\MailboxesTable|\Cake\ORM\Association\BelongsTo $Mailboxes
 * @property \MessagingCenter\Model\Table\FoldersTable|\Cake\ORM\Association\BelongsTo $ParentFolders
 * @property \MessagingCenter\Model\Table\FoldersTable|\Cake\ORM\Association\HasMany $ChildFolders
 *
 * @method \MessagingCenter\Model\Entity\Folder get($primaryKey, $options = [])
 * @method \MessagingCenter\Model\Entity\Folder newEntity($data = null, array $options = [])
 * @method \MessagingCenter\Model\Entity\Folder[] newEntities(array $data, array $options = [])
 * @method \MessagingCenter\Model\Entity\Folder|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \MessagingCenter\Model\Entity\Folder|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \MessagingCenter\Model\Entity\Folder patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \MessagingCenter\Model\Entity\Folder[] patchEntities($entities, array $data, array $options = [])
 * @method \MessagingCenter\Model\Entity\Folder findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class FoldersTable extends Table
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

        $this->setTable('qobo_folders');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Mailboxes', [
            'foreignKey' => 'mailbox_id',
            'joinType' => 'INNER',
            'className' => 'MessagingCenter.Mailboxes'
        ]);
        $this->belongsTo('ParentFolders', [
            'className' => 'MessagingCenter.Folders',
            'foreignKey' => 'parent_id'
        ]);
        $this->hasMany('ChildFolders', [
            'className' => 'MessagingCenter.Folders',
            'foreignKey' => 'parent_id'
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
        $rules->add($rules->existsIn(['mailbox_id'], 'Mailboxes'));
        $rules->add($rules->existsIn(['parent_id'], 'ParentFolders'));

        return $rules;
    }

    /**
     * createDefaultFolders method
     *
     * @param \Cake\Datasource\EntityInterface $mailbox entity.
     * @return mixed[]
     */
    public function createDefaultFolders(EntityInterface $mailbox) : array
    {
        $list = [];
        foreach (MailboxesTable::getDefaultFolders() as $folderName) {
            $query = $this->find()
                ->where([
                    'name' => $folderName,
                    'mailbox_id' => $mailbox->get('id'),
                ]);

            $result = $query->first();

            if (empty($result)) {
                $folder = $this->newEntity();
                $this->patchEntity($folder, [
                    'mailbox_id' => $mailbox->get('id'),
                    'name' => $folderName,
                    'type' => (string)Configure::read('MessagingCenter.Folder.defaultType'),
                ]);

                $result = $this->save($folder);
            }

            $list[$folderName] = $result;
        }

        if (empty($list)) {
            throw new InvalidArgumentException('Cannot create default folders for mailbox ' . $mailbox->get('name') . '!');
        }

        return $list;
    }
}
