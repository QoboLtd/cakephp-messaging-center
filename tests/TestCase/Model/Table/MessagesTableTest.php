<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use MessagingCenter\Model\Table\FoldersTable;
use MessagingCenter\Model\Table\MessagesTable;

/**
 * MessagingCenter\Model\Table\MessagesTable Test Case
 */
class MessagesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \MessagingCenter\Model\Table\MessagesTable
     */
    public $Messages;

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

        $config = TableRegistry::exists('MessagingCenter.Messages') ? [] : ['className' => 'MessagingCenter\Model\Table\MessagesTable'];
        /**
         * @var \MessagingCenter\Model\Table\MessagesTable $table
         */
        $table = TableRegistry::get('MessagingCenter.Messages', $config);
        $this->Messages = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Messages);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertTrue($this->Messages->hasBehavior('Timestamp'), 'Missing behavior Timestamp.');
        $this->assertInstanceOf(MessagesTable::class, $this->Messages);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $result = $this->Messages->validationDefault($validator);

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
        $result = $this->Messages->buildRules($rules);

        $this->assertInstanceOf(RulesChecker::class, $result);
    }

    public function testGetDateSent(): void
    {
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $this->Messages->getDateSent()->i18nFormat());
    }

    /**
     * test processMessages() method
     *
     * @return void
     */
    public function testProcessMessages() : void
    {
        $userId = '00000000-0000-0000-0000-000000000001';

        $folders = $this->getDefaultFolders();

        $result = $this->Messages->processMessages($userId, $folders);

        $this->assertTrue($result, 'Cannot process messages!');
    }

    /**
     * method to get default folders for specified mailbox
     *
     * @return mixed[]
     */
    protected function getDefaultFolders() : array
    {
        $mailboxId = '00000000-0000-0000-0000-000000000001';

        $config = TableRegistry::getTableLocator()->exists('Folders') ? [] : ['className' => FoldersTable::class];
        $foldersTable = TableRegistry::getTableLocator()->get('Folders', $config);
        $query = $foldersTable->find()
            ->where([
                'mailbox_id' => $mailboxId,
            ]);

        $folders = [];
        foreach ($query as $folder) {
            $folders[$folder->get('name')] = $folder;
        }

        return $folders;
    }

    public function testSystemValidations() : void
    {
        $entity = $this->Messages->newEntity([
            'type' => 'system',
            'subject' => '',
            'content' => 'Foo Foo!',
            'status' => 'new',
        ]);
        $this->assertTrue(array_key_exists('from_user', $entity->getErrors()));
        $this->assertTrue(array_key_exists('to_user', $entity->getErrors()));
    }

    public function testEmailValidations() : void
    {
        $entity = $this->Messages->newEntity([
            'type' => 'email',
            'subject' => '',
            'content' => 'Foo Foo!',
            'status' => 'new',
        ]);
        $this->assertTrue(array_key_exists('from_user', $entity->getErrors()));
        $this->assertTrue(array_key_exists('to_user', $entity->getErrors()));
    }

    public function testSystemMessageSender(): void
    {
        $message = $this->Messages->get(
            '00000000-0000-0000-0000-000000000001',
            ['contain' => ['FromUser']]
        );
        $this->assertEquals('first1 last1', $message->get('sender'));
        $this->assertEquals('user-1@test.com', $message->get('sender_address'));
    }

    public function testEmailMessageSender(): void
    {
        $message = $this->Messages->get(
            '00000000-0000-0000-0000-000000000007',
            ['contain' => ['FromUser']]
        );
        $this->assertEquals('Test2019', $message->get('sender'));
        $this->assertEquals('test2019me@ya.ru', $message->get('sender_address'));
    }

    public function testEmailHeader(): void
    {
        $message = $this->Messages->get('00000000-0000-0000-0000-000000000011');
        $recipientAddresses = $message->get('recipient_addresses');
        $this->assertEquals([], $recipientAddresses);
    }
}
