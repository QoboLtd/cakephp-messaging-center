<?php
namespace MessagingCenter\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use MessagingCenter\Model\Entity\Folder;
use Webmozart\Assert\Assert;

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
            'className' => 'MessagingCenter.Mailboxes',
        ]);
        $this->belongsTo('ParentFolders', [
            'className' => 'MessagingCenter.Folders',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('ChildFolders', [
            'className' => 'MessagingCenter.Folders',
            'foreignKey' => 'parent_id',
        ]);
        $this->hasMany('Messages', [
            'className' => 'MessagingCenter.Messages',
            'foreignKey' => 'folder_id',
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
            ->uuid('mailbox_id')
            ->requirePresence('mailbox_id', 'create')
            ->notEmpty('mailbox_id');

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
     * @return \MessagingCenter\Model\Entity\Folder[]
     */
    public function createDefaultFolders(EntityInterface $mailbox): array
    {
        $list = [];
        $order = 0;
        foreach (MailboxesTable::getDefaultFolders() as $folderName) {
            $query = $this->find()
                ->where([
                    'name' => $folderName,
                    'mailbox_id' => $mailbox->get('id'),
                ]);

            $result = $query->first();
            Assert::nullOrIsInstanceOf($result, EntityInterface::class);

            if (empty($result)) {
                $folder = $this->newEntity();
                Assert::isInstanceOf($folder, EntityInterface::class);

                $this->patchEntity($folder, [
                    'mailbox_id' => $mailbox->get('id'),
                    'name' => $folderName,
                    'type' => (string)Configure::read('MessagingCenter.Folder.defaultType'),
                    'order_no' => $order++,
                    'icon' => strtolower($folderName),
                ]);

                $result = $this->save($folder);
                Assert::isInstanceOf($result, EntityInterface::class);
            }

            $list[] = $result;
        }

        Assert::notEmpty($list, 'Cannot create default folders for mailbox ' . $mailbox->get('name') . '!');
        Assert::allIsInstanceOf($list, Folder::class);

        return $list;
    }
}
