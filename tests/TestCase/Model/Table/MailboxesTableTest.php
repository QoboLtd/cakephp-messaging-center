<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Table\MailboxesTable;

/**
 * MessagingCenter\Model\Table\MailboxesTable Test Case
 */
class MailboxesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \MessagingCenter\Model\Table\MailboxesTable
     */
    public $Mailboxes;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.messaging_center.mailboxes',
        'plugin.messaging_center.folders',
        'plugin.messaging_center.messages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $config = TableRegistry::getTableLocator()->exists('Mailboxes') ? [] : ['className' => MailboxesTable::class];

        /**
         * @var \MessagingCenter\Model\Table\MailboxesTable $table
         */
        $table = TableRegistry::getTableLocator()->get('Mailboxes', $config);
        $this->Mailboxes = $table;

        Configure::write('MessagingCenter', [
            'Mailbox' => [
                'default' => [
                    'mailbox_type' => 'system',
                    'incoming_transport' => 'internal',
                    'incoming_settings' => 'default',
                    'outgoing_transport' => 'internal',
                    'outgoing_settings' => 'default',
                    'mailbox_postfix' => '@system',
                ]
            ],
            'Folder' => [
                'defaultType' => 'default',
            ],
            'systemUser' => [
                'name' => 'System',
                'id' => '00000000-0000-0000-0000-000000000000',
            ],
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Mailboxes);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertTrue($this->Mailboxes->hasBehavior('Timestamp'), 'Missing behavior Timestamp.');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Mailboxes->getAssociation('Users'));
        $this->assertInstanceOf(MailboxesTable::class, $this->Mailboxes);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $result = $this->Mailboxes->validationDefault($validator);

        $this->assertInstanceOf(Validator::class, $result);
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $rules = new RulesChecker();
        $result = $this->Mailboxes->buildRules($rules);

        $this->assertInstanceOf(RulesChecker::class, $result);
    }

    /**
     * Test create default mailbox
     *
     * @return void
     */
    public function testCreateDefaultMailbox() : void
    {
        $userTable = TableRegistry::getTableLocator()->get('Users');
        $user = $userTable->get('00000000-0000-0000-0000-000000000001');

        $result = $this->Mailboxes->createDefaultMailbox($user->toArray());

        $this->assertNotEmpty($result, 'Cannot create a default mailbox');
        $this->assertEquals($result->get('name'), 'user-1@system', 'System mailbox name is not matched');
    }

    /**
     * testGetSystemMailbox method
     *
     * @return void
     */
    public function testGetSystemMailbox() : void
    {
        $userTable = TableRegistry::getTableLocator()->get('Users');
        $user = $userTable->get('00000000-0000-0000-0000-000000000002');

        $result = $this->Mailboxes->getSystemMailbox($user->toArray());

        $this->assertNotEmpty($result, 'Cannot get system mailbox');
        $this->assertInstanceOf(EntityInterface::class, $result, 'Fetched mailbox is invalid');
        $this->assertEquals($result->get('type'), 'system', 'Fetched mailbox is not system');
    }

    public function testCountUnreadMessages() : void
    {
        $mailbox = $this->Mailboxes->get('00000000-0000-0000-0000-000000000001');
        $unreadCount = $this->Mailboxes->countUnreadMessages($mailbox);
        $this->assertEquals(3, $unreadCount);
    }

    public function testGetUnreadMessages() : void
    {
        $mailbox = $this->Mailboxes->get('00000000-0000-0000-0000-000000000001');
        $unread = $this->Mailboxes->getUnreadMessages($mailbox);
        $this->assertEquals(3, count($unread));
    }

    public function testGetUnreadMessagesWithLimit() : void
    {
        $mailbox = $this->Mailboxes->get('00000000-0000-0000-0000-000000000001');
        $limit = 1;
        $unread = $this->Mailboxes->getUnreadMessages($mailbox, $limit);
        $this->assertEquals($limit, count($unread));
    }

    public function testGetFolders() : void
    {
        $mailbox = $this->Mailboxes->get('00000000-0000-0000-0000-000000000001');
        $folders = $this->Mailboxes->getFolders($mailbox);
        $this->assertEquals(2, count($folders));
    }

    public function testGetAllActiveMailboxes(): void
    {
        $mailboxes = $this->Mailboxes->getActiveMailboxes();
        $this->assertEquals(2, $mailboxes->count());
    }

    public function testGetActiveMailboxesByType(): void
    {
        $mailboxes = $this->Mailboxes->getActiveMailboxes((string)MailboxType::EMAIL());
        $this->assertEquals(1, $mailboxes->count());
    }
}
