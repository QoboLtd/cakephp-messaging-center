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
namespace MessagingCenter\Model\Behavior;

use ArrayObject;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;
use MessagingCenter\Event\EventName;
use MessagingCenter\Notifier\MessageNotifier;

class NotifyBehavior extends Behavior
{
    use EventDispatcherTrait;

    /**
     * Assigned status identifier.
     */
    const STATUS_ASSIGNED = 'assigned';

    /**
     * Modified status identifier.
     */
    const STATUS_MODIFIED = 'modified';

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
     * From user id.
     *
     * @var string
     */
    protected $_fromUser = null;

    /**
     * Ingored modified fields.
     *
     * @var array
     */
    protected $_ignoredModifyFields = ['created', 'modified', 'modified_by', 'created_by'];

    /**
     * Ingored notify fields.
     *
     * @var array
     */
    protected $_ignoredNotifyFields = ['modified_by', 'created_by'];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        // merge with default ignored fields
        if (!empty($this->config('ignoredFields'))) {
            $this->_ignoredModifyFields = array_merge($this->_ignoredModifyFields, $this->config('ignoredFields'));
        }

        $this->_fromUser = Configure::readOrFail('MessagingCenter.systemUser.id');
        // get users table
        $this->_usersTable = TableRegistry::get('Users');

        $this->Notifier = new MessageNotifier();
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options)
    {
        // nothing has been modified
        if (!$entity->dirty()) {
            return;
        }

        $modifiedFields = $this->_getModifiedFields($entity);

        // skip if empty modified fields
        if (empty($modifiedFields)) {
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
            $this->_notifyUser($notifyField, $entity, $event->subject(), $modifiedFields);
        }
    }

    /**
     * Get entity modified fields in multi-dimensional array format,
     * with field name as the key and old value / new value as value.
     *
     * Returns empty result if all modified fields are part of the
     * _ignoreFields array.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return array
     */
    protected function _getModifiedFields(EntityInterface $entity)
    {
        if ($entity->isNew()) {
            $fields = $entity->extractOriginal($entity->visibleProperties());
        } else {
            $fields = $entity->extractOriginalChanged($entity->visibleProperties());
        }

        $diff = array_diff(array_keys($fields), $this->_ignoredModifyFields);

        if (empty($diff)) {
            return [];
        }

        $result = [];
        foreach ($fields as $k => $v) {
            // skip ignored fields
            if (!in_array($k, $diff)) {
                continue;
            }
            $result[$k] = [
                'oldValue' => $v,
                'newValue' => $entity->{$k}
            ];
        }

        return $result;
    }

    /**
     * Get notify fields based on current table's associations.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return array
     */
    protected function _getNotifyFields(Table $table)
    {
        $result = [];
        foreach ($table->associations() as $association) {
            if ($association->className() !== $this->_usersTable->alias()) {
                continue;
            }

            if (in_array($association->foreignKey(), $this->_ignoredModifyFields)) {
                continue;
            }

            $result[] = $association->foreignKey();
        }

        return $result;
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
        $result = [];
        foreach ($notifyFields as $notifyField) {
            // skip notify field(s) with empty value
            if (empty($entity->get($notifyField))) {
                continue;
            }

            $result[] = $notifyField;
        }

        return $result;
    }

    /**
     * Get template file based on notify field status
     *
     * @param string $field Notify field
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return array
     */
    protected function _getTemplate($field, EntityInterface $entity)
    {
        return $entity->isDirty($field) ? 'MessagingCenter.record_link' : 'MessagingCenter.record_modified';
    }

    /**
     * Notify user with a new message.
     *
     * @param string $field Field name
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \Cake\ORM\Table $table Table instance
     * @param array $modifiedFields Entity's modified fields (includes old and new values)
     * @return void
     */
    protected function _notifyUser($field, EntityInterface $entity, Table $table, array $modifiedFields)
    {
        $this->Notifier->from($this->_fromUser);
        $this->Notifier->to($entity->get($field));
        $this->Notifier->template($this->_getTemplate($field, $entity));

        $data = $this->_getData($field, $entity, $table, $modifiedFields);

        // broadcast event for modifying message data before passing them to the Notifier
        $event = new Event((string)EventName::NOTIFY_BEFORE_RENDER(), $this, [
            'table' => $this->getTable(),
            'entity' => $entity,
            'data' => $data
        ]);
        $this->eventManager()->dispatch($event);
        $data = !empty($event->result) ? $event->result : $data;

        $this->Notifier->subject($data['modelName'] . ': ' . $data['recordName']);
        $this->Notifier->message($data);

        $this->Notifier->send();
    }

    /**
     * Notification message data getter.
     *
     * @param string $field Field name
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \Cake\ORM\Table $table Table instance
     * @param array $modifiedFields Entity's modified fields (includes old and new values)
     * @return array
     */
    protected function _getData($field, EntityInterface $entity, Table $table, array $modifiedFields)
    {
        return [
            'modelName' => Inflector::singularize(Inflector::humanize(Inflector::underscore($table->table()))),
            'registryAlias' => $table->registryAlias(),
            'recordId' => $entity->get($table->getPrimaryKey()),
            'recordName' => $entity->get($table->getDisplayField()),
            'field' => Inflector::humanize($field),
            'modifiedFields' => $modifiedFields
        ];
    }
}
