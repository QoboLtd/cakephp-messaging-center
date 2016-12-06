<?php
namespace MessagingCenter\Notifier;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\View\ViewVarsTrait;
use InvalidArgumentException;

class MessageNotifier extends Notifier
{
    use ViewVarsTrait;

    /**
     * Messages Table instance.
     *
     * @var \Cake\ORM\Table
     */
    protected $_table = null;

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
     * {@inheritDoc}
     */
    protected $_requiredFields = [
        'from',
        'to',
        'subject',
        'message',
        'status',
        'dateSent'
    ];

    /**
     * Map class to entity properties.
     *
     * @var array
     */
    protected $_propertyMap = [
        'from' => 'from_user',
        'to' => 'to_user',
        'message' => 'content',
        'dateSent' => 'date_sent',
        'subject' => 'subject',
        'status' => 'status'
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        // check if required fields are present
        if (!empty($diff)) {
            throw new InvalidArgumentException('Fields [' . implode(', ', $diff) . '] are required.');
        }

        // set table instance
        $this->_table = TableRegistry::get('MessagingCenter.Messages');

        // set properties
        $this->_dateSent = new Time();
        $this->_status = $this->_table->getNewStatus();

        $this->viewBuilder()
            ->className('Cake\View\View')
            ->template('')
            ->layout('default')
            ->helpers(['Html']);
    }

    /**
     * {@inheritDoc}
     */
    public function template($template = 'MessagingCenter.record_link')
    {
        parent::template($template);
    }

    /**
     * {@inheritDoc}
     */
    public function send()
    {
        // validate message data
        $this->validate();

        $entity = $this->_table->newEntity();
        $data = $this->_getMessageData();
        $entity = $this->_table->patchEntity($entity, $data);

        $this->_table->save($entity);
    }

    /**
     * Extracts and returns notification message data from class properties.
     *
     * @return array
     */
    protected function _getMessageData()
    {
        $data = [];
        foreach ($this->_propertyMap as $k => $v) {
            $property = '_' . $k;
            $data[$v] = $this->{$property};
        }

        return $data;
    }
}