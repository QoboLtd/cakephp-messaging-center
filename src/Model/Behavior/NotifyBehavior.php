<?php
namespace MessagingCenter\Model\Behavior;

use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use MessagingCenter\Notifier\MessageNotifier;

class NotifyBehavior extends Behavior
{
    /**
     * Notifier instance.
     *
     * @var \MessagingCenter\Notifier\Notifier
     */
    protected $Notifier = null;

    /**
     * Users table instance.
     *
     * @var \Cake\ORM\Table
     */
    protected $_usersTable = null;

    /**
     * From user entity.
     *
     * @var Cake\ORM\Entity
     */
    protected $_fromUser = null;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        // get users table
        $this->_usersTable = TableRegistry::get('Users');

        $this->Notifier = new MessageNotifier();

        // get [from] user to be used on system notifications
        $username = 'SYSTEM';
        $this->_fromUser = $this->_usersTable->find('all', [
            'conditions' => [
                'username' => $username
            ]
        ])->first();

        // log user not found error
        if (!$this->_fromUser) {
            Log::error('[' . $username . '] user was not found in the system, notifications cannot be sent.');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        // from user was not found
        if (!$this->_fromUser) {
            return;
        }

        // nothing has been modified
        if (!$entity->dirty()) {
            return;
        }

        $notifyFields = $this->_getNotifyFields($event->subject());
        // no notify fields have been found
        if (empty($notifyFields)) {
            return;
        }

        $notifyFields = $this->_filterNotifyFields($notifyFields, $entity);
        // notify field(s) have not been modified or their value is empty
        if (empty($notifyFields)) {
            return;
        }

        foreach ($notifyFields as $notifyField) {
            $this->_notifyUser($notifyField, $entity, $event->subject());
        }
    }

    /**
     * Get notify fields based on current table's associations.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getNotifyFields(Table $table)
    {
        $fields = [];
        foreach ($table->associations() as $association) {
            if ($association->className() !== $this->_usersTable->alias()) {
                continue;
            }
            $fields[] = $association->foreignKey();
        }

        return $fields;
    }

    /**
     * Filter out notify fields that have not been modified or their value is currently empty.
     *
     * @param  array $notifyFields Notify fields
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return array
     */
    protected function _filterNotifyFields(array $notifyFields, EntityInterface $entity)
    {
        $fields = [];
        foreach ($notifyFields as $notifyField) {
            // skip notify field(s) that have NOT been modified
            if (!$entity->dirty($notifyField)) {
                continue;
            }

            // skip notify field(s) with empty value
            if (empty($entity->{$notifyField})) {
                continue;
            }

            $fields[] = $notifyField;
        }

        return $fields;
    }

    /**
     * Notify user with a new message.
     *
     * @param string $field Field name
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \Cake\ORM\Table $table Table instance
     * @return void
     */
    protected function _notifyUser($field, EntityInterface $entity, Table $table)
    {
        $modelName = Inflector::singularize(Inflector::humanize(Inflector::underscore($table->table())));
        $this->Notifier->from($this->_fromUser->id);
        $this->Notifier->to($entity->{$field});
        $this->Notifier->subject($modelName . ' Record');
        $this->Notifier->message([
            'modelName' => $modelName,
            'registryAlias' => $table->registryAlias(),
            'recordId' => $entity->{$table->primaryKey()},
            'recordName' => $entity->{$table->displayField()}
        ]);

        $this->Notifier->send();
    }
}
