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
namespace MessagingCenter\Notifier;

use Cake\Event\Event;
use Cake\Event\EventDispatcherTrait;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use MessagingCenter\Event\EventName;
use Webmozart\Assert\Assert;

class MessageNotifier extends Notifier
{
    use EventDispatcherTrait;

    /**
     * Messages Table instance.
     *
     * @var \MessagingCenter\Model\Table\MessagesTable $_table
     */
    protected $_table;

    /**
     * Notification status.
     *
     * @var string
     */
    protected $_status;

    /**
     * Notification date sent.
     *
     * @var \Cake\I18n\Time
     */
    protected $_dateSent;

    /**
     * Notification folder.
     *
     * @var string
     */
    protected $_folder;

    /**
     * {@inheritDoc}
     */
    protected $_requiredFields = [
        'from',
        'to',
        'subject',
        'message',
        'status',
        'dateSent',
        'folder',
    ];

    /**
     * Map class to entity properties.
     *
     * Key is Class property, Value is Message entity field
     *
     * @var array
     */
    protected $_propertyMap = [
        'from' => 'from_user',
        'to' => 'to_user',
        'message' => 'content',
        'dateSent' => 'date_sent',
        'subject' => 'subject',
        'status' => 'status',
        'folder' => 'folder_id',
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * @var \MessagingCenter\Model\Table\MessagesTable $table
         */
        $table = TableRegistry::get('MessagingCenter.Messages');
        $this->_table = $table;

        // set properties
        $this->_dateSent = new Time();
        $this->_status = $this->_table->getNewStatus();

        $this->viewBuilder()
            ->setClassName('Cake\View\View')
            ->setTemplate('')
            ->setLayout('default')
            ->setHelpers(['Html']);
    }

    /**
     * Message template setter.
     *
     * @param  string $template Template name
     * @return void
     */
    public function template(string $template): void
    {
        if (empty($template)) {
            $template = 'MessagingCenter.record_link';
        }
        parent::template($template);
    }

    /**
     * Message folder setter.
     *
     * @param  string $folder Folder name
     * @return void
     */
    public function folder(string $folder): void
    {
        Assert::uuid($folder);
        $this->_folder = $folder;
    }

    /**
     * Sends notification.
     *
     * @return void
     */
    public function send(): void
    {
        // validate message data
        $this->validate();

        $entity = $this->_table->newEntity();
        $data = $this->getMessageData();
        $entity = $this->_table->patchEntity($entity, $data);

        $this->_table->save($entity);

        if ($this->_table->save($entity)) {
            $event = new Event((string)EventName::NEW_NOTIFICATION_RECEIVED(), $this, [
                'table' => $this->_table,
                'entity' => $entity,
                'data' => $data,
            ]);
            $this->getEventManager()->dispatch($event);
        }
    }

    /**
     * Extracts and returns notification message data from class properties.
     *
     * @return mixed[]
     */
    protected function getMessageData(): array
    {
        $data = [];
        foreach ($this->_propertyMap as $k => $v) {
            $property = '_' . $k;
            $data[$v] = $this->{$property};
        }

        return $data;
    }
}
