<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\I18n\Time;
use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
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
}
