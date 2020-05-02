<?php
namespace MessagingCenter\Test\TestCase\Event\Model;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventList;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MessagingCenter\Event\Model\MailboxListener;

class MailboxListenerTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.MessagingCenter.Mailboxes',
        'plugin.MessagingCenter.Folders',
        'plugin.MessagingCenter.Messages',
    ];

    /**
     * @var \Cake\ORM\Table $mailboxes
     */
    protected $mailboxes;

    public function setUp()
    {
        parent::setUp();

        Configure::write('Users.table', 'CakeDC/Users.Users');
        $this->mailboxes = TableRegistry::get('MessagingCenter.Mailboxes');

        // enable event tracking
        $this->mailboxes->getEventManager()->setEventList(new EventList());
        EventManager::instance()->on(new MailboxListener());
    }

    public function tearDown()
    {
        unset($this->mailboxes);

        parent::tearDown();
    }

    /**
     * testCreateFolders method
     *
     * @return void
     */
    public function testCreateFolders(): void
    {
        $data = [
            'user_id' => '00000000-0000-0000-0000-000000000001',
            'name' => 'mytest@system',
            'type' => 'system',
            'incoming_transport' => 'internal',
            'incoming_settings' => Configure::read('MessagingCenter.Mailbox.default.incoming_settings'),
            'outgoing_transport' => 'internal',
            'outgoing_settings' => Configure::read('MessagingCenter.Mailbox.default.outgoing_settings'),
            'active' => 1,
        ];

        $mailbox = $this->mailboxes->newEntity();
        $this->mailboxes->patchEntity($mailbox, $data);
        $result = $this->mailboxes->save($mailbox, $data);

        $this->assertEventFired('Model.afterSave', $this->mailboxes->getEventManager());

        $this->assertInstanceOf(EntityInterface::class, $result, 'Cannot create a new mailbox');
    }
}
