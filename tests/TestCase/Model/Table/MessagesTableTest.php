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
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Messages->getAssociation('FromUser'));
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Messages->getAssociation('ToUser'));
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

    public function testGetNewStatus(): void
    {
        $this->assertEquals('new', $this->Messages->getNewStatus());
    }

    public function testGetReadStatus(): void
    {
        $this->assertEquals('read', $this->Messages->getReadStatus());
    }

    public function testGetDeletedStatus(): void
    {
        $this->assertEquals('deleted', $this->Messages->getDeletedStatus());
    }

    public function testGetArchivedStatus(): void
    {
        $this->assertEquals('archived', $this->Messages->getArchivedStatus());
    }

    public function testGetDateSent(): void
    {
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $this->Messages->getDateSent()->i18nFormat());
    }

    public function testGetSentFolder(): void
    {
        $this->assertEquals('sent', $this->Messages->getSentFolder());
    }

    public function testGetDefaultFolder(): void
    {
        $this->assertEquals('inbox', $this->Messages->getDefaultFolder());
    }

    public function testGetFolders(): void
    {
        $this->assertEquals(['inbox', 'archived', 'sent', 'trash'], $this->Messages->getFolders());
    }

    public function testFolderExists(): void
    {
        $this->assertTrue($this->Messages->folderExists('inbox'));
    }

    public function testFolderExistsNot(): void
    {
        $this->assertFalse($this->Messages->folderExists('foo'));
    }

    public function testGetFolderByMessageFromUser(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000001');
        $result = $this->Messages->getFolderByMessage($entity, '00000000-0000-0000-0000-000000000001');

        $this->assertEquals('sent', $result);
    }

    public function testGetFolderByMessageToUser(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000001');
        $result = $this->Messages->getFolderByMessage($entity, '00000000-0000-0000-0000-000000000002');

        $this->assertEquals('inbox', $result);
    }

    public function testGetFolderByDeletedMessageFromUser(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000002');
        $result = $this->Messages->getFolderByMessage($entity, '00000000-0000-0000-0000-000000000001');

        $this->assertEquals('sent', $result);
    }

    public function testGetFolderByDeletedMessageToUser(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000002');
        $result = $this->Messages->getFolderByMessage($entity, '00000000-0000-0000-0000-000000000002');

        $this->assertEquals('trash', $result);
    }

    public function testGetFolderByArchivedMessageFromUser(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000003');
        $result = $this->Messages->getFolderByMessage($entity, '00000000-0000-0000-0000-000000000001');

        $this->assertEquals('sent', $result);
    }

    public function testGetFolderByArchivedMessageToUser(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000003');
        $result = $this->Messages->getFolderByMessage($entity, '00000000-0000-0000-0000-000000000002');

        $this->assertEquals('archived', $result);
    }

    public function testGetFolderByReferer(): void
    {
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $this->Messages->get('00000000-0000-0000-0000-000000000001');
        $userId = '00000000-0000-0000-0000-000000000001';
        $referer = '/folder/archived';
        $result = $this->Messages->getFolderByMessage($entity, $userId, $referer);

        $this->assertEquals('archived', $result);
    }

    public function testGetConditionsByFolderDefault(): void
    {
        $folder = '';
        $userId = '00000000-0000-0000-0000-000000000001';

        $expected = [
            'to_user' => $userId,
            'status IN' => ['read', 'new']
        ];

        $this->assertEquals($expected, $this->Messages->getConditionsByFolder($userId, $folder));
    }

    public function testGetConditionsByFolderInbox(): void
    {
        $folder = 'inbox';
        $userId = '00000000-0000-0000-0000-000000000001';

        $expected = [
            'to_user' => $userId,
            'status IN' => ['read', 'new']
        ];

        $this->assertEquals($expected, $this->Messages->getConditionsByFolder($userId, $folder));
    }

    public function testGetConditionsByFolderArchived(): void
    {
        $folder = 'archived';
        $userId = '00000000-0000-0000-0000-000000000001';

        $expected = [
            'to_user' => $userId,
            'status' => 'archived'
        ];

        $this->assertEquals($expected, $this->Messages->getConditionsByFolder($userId, $folder));
    }

    public function testGetConditionsByFolderSent(): void
    {
        $folder = 'sent';
        $userId = '00000000-0000-0000-0000-000000000001';

        $expected = [
            'from_user' => $userId,
        ];

        $this->assertEquals($expected, $this->Messages->getConditionsByFolder($userId, $folder));
    }

    public function testGetConditionsByFolderTrash(): void
    {
        $folder = 'trash';
        $userId = '00000000-0000-0000-0000-000000000001';

        $expected = [
            'to_user' => $userId,
            'status' => 'deleted'
        ];

        $this->assertEquals($expected, $this->Messages->getConditionsByFolder($userId, $folder));
    }

    public function testProcessMessages()
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

    protected function getDefaultFolders()
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

    protected function getMessagesCount($where)
    {
        $msgNum = $this->Messages->find()
            ->where($where)
            ->count();

        return $msgNum;
    }
}
