<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\Core\Configure;
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

        /**
         * 5 messages in fixture without folder_id and 1 is assigned to folder
         */
        $this->assertEquals(5, $this->getMessagesCount(['folder_id IS' => null]), 'Wrong number of messages without folder!');

        $result = $this->Messages->processMessages($userId, $folders);

        $this->assertTrue($result, 'Cannot process messages!');

        /**
         * 1 messages shouldn't be duplicated because of system messages
         * 4 messages have to be duplicated
         */
        $this->assertEquals(9, $this->getMessagesCount(['folder_id IS NOT' => null]), 'Wrong number of messages without folder!');
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

    /**
     * method to get count of messages with specified criteria
     *
     * @param mixed[] $where criteria to search messages
     * @return int
     */
    protected function getMessagesCount(array $where) : int
    {
        $msgNum = $this->Messages->find()
            ->where($where)
            ->count();

        return $msgNum;
    }
}
