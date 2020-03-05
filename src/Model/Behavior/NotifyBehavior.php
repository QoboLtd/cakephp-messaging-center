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
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Notifier\MessageNotifier;
use Webmozart\Assert\Assert;

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
     * @var \MessagingCenter\Notifier\MessageNotifier
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
        if (!empty($this->getConfig('ignoredFields'))) {
            $this->_ignoredModifyFields = array_merge($this->_ignoredModifyFields, $this->getConfig('ignoredFields'));
        }

        $this->_fromUser = Configure::readOrFail('MessagingCenter.systemUser.id');
        // get users table
        $this->_usersTable = TableRegistry::get('Users');

        $this->Notifier = new MessageNotifier();
    }

    /**
     * afterSave event
     *
     * @param \Cake\Event\Event $event Event
     * @param \Cake\Datasource\EntityInterface $entity Entity
     * @param \ArrayObject $options Options
     * @return void
     */
    public function afterSave(Event $event, EntityInterface $entity, ArrayObject $options): void
    {
        // nothing has been modified
        if (!$entity->isDirty()) {
            return;
        }

        $modifiedFields = $this->_getModifiedFields($entity);

        // skip if empty modified fields
        if (empty($modifiedFields)) {
            return;
        }

        /**
         * @var \Cake\ORM\Table $subject
         */
        $subject = $event->getSubject();

        $notifyFields = $this->_getNotifyFields($subject);
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
            $this->_notifyUser($notifyField, $entity, $subject, $modifiedFields);
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
     * @return mixed[]
     */
    protected function _getModifiedFields(EntityInterface $entity): array
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
                'newValue' => $entity->{$k},
            ];
        }

        return $result;
    }

    /**
     * Get notify fields based on current table's associations.
     *
     * @param \Cake\ORM\Table $table Table instance
     * @return mixed[]
     */
    protected function _getNotifyFields(Table $table): array
    {
        $result = [];
        foreach ($table->associations() as $association) {
            if ($association->className() !== $this->_usersTable->getAlias()) {
                continue;
            }

            if (in_array($association->getForeignKey(), $this->_ignoredModifyFields)) {
                continue;
            }

            $result[] = $association->getForeignKey();
        }

        return $result;
    }

    /**
     * Filter out notify fields that have not been modified or their value is currently empty.
     *
     * @param  mixed[] $notifyFields Notify fields
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @return mixed[]
     */
    protected function _filterNotifyFields(array $notifyFields, EntityInterface $entity): array
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
     * @return string
     */
    protected function _getTemplate(string $field, EntityInterface $entity): string
    {
        return $entity->isDirty($field) ? 'MessagingCenter.record_link' : 'MessagingCenter.record_modified';
    }

    /**
     * Notify user with a new message.
     *
     * @param string $field Field name
     * @param \Cake\Datasource\EntityInterface $entity Entity object
     * @param \Cake\ORM\Table $table Table instance
     * @param mixed[] $modifiedFields Entity's modified fields (includes old and new values)
     * @return void
     */
    protected function _notifyUser(string $field, EntityInterface $entity, Table $table, array $modifiedFields): void
    {
        $user = $this->_usersTable->get($entity->get($field));

        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);
        $defaultMailbox = $mailboxes->createDefaultMailbox($user->toArray());

        $this->Notifier->from($this->_fromUser);
        $this->Notifier->to($entity->get($field));
        $this->Notifier->template($this->_getTemplate($field, $entity));
        $this->Notifier->folder($mailboxes->getFolderByName($defaultMailbox, MailboxesTable::FOLDER_INBOX)->get('id'));

        $data = $this->_getData($field, $entity, $table, $modifiedFields);

        // broadcast event for modifying message data before passing them to the Notifier
        $event = new Event((string)EventName::NOTIFY_BEFORE_RENDER(), $this, [
            'table' => $this->getTable(),
            'entity' => $entity,
            'data' => $data,
        ]);
        $this->getEventManager()->dispatch($event);
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
     * @param mixed[] $modifiedFields Entity's modified fields (includes old and new values)
     * @return mixed[]
     */
    protected function _getData(string $field, EntityInterface $entity, Table $table, array $modifiedFields): array
    {
        /**
         * @var string $primaryKey
         */
        $primaryKey = $table->getPrimaryKey();

        return [
            'modelName' => Inflector::singularize(Inflector::humanize(Inflector::underscore($table->getTable()))),
            'registryAlias' => $table->getRegistryAlias(),
            'recordId' => $entity->get($primaryKey),
            'recordName' => $entity->get($table->getDisplayField()),
            'field' => Inflector::humanize($field),
            'modifiedFields' => $modifiedFields,
        ];
    }
}
