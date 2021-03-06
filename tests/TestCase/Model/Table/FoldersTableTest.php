<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use MessagingCenter\Model\Table\FoldersTable;
use MessagingCenter\Model\Table\MailboxesTable;

/**
 * MessagingCenter\Model\Table\FoldersTable Test Case
 */
class FoldersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \MessagingCenter\Model\Table\FoldersTable
     */
    public $Folders;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.MessagingCenter.Mailboxes',
        'plugin.MessagingCenter.Folders',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Folders') ? [] : ['className' => FoldersTable::class];
        /**
         * @var \MessagingCenter\Model\Table\FoldersTable $table
         */
        $table = TableRegistry::getTableLocator()->get('Folders', $config);
        $this->Folders = $table;
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Folders);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertTrue($this->Folders->hasBehavior('Timestamp'), 'Missing behavior Timestamp.');
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Folders->getAssociation('Mailboxes'));
        $this->assertInstanceOf('Cake\ORM\Association\BelongsTo', $this->Folders->getAssociation('ParentFolders'));
        $this->assertInstanceOf('Cake\ORM\Association\HasMany', $this->Folders->getAssociation('ChildFolders'));
        $this->assertInstanceOf(FoldersTable::class, $this->Folders);
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $result = $this->Folders->validationDefault($validator);

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
        $result = $this->Folders->buildRules($rules);

        $this->assertInstanceOf(RulesChecker::class, $result);
    }

    /**
     * test create default folders
     *
     * @return void
     */
    public function testCreateDefaultFolders(): void
    {
        $config = TableRegistry::getTableLocator()->exists('Mailboxes') ? [] : ['className' => MailboxesTable::class];
        $mailboxTable = TableRegistry::getTableLocator()->get('Mailboxes', $config);
        $mailbox = $mailboxTable->get('00000000-0000-0000-0000-000000000001');

        $result = $this->Folders->createDefaultFolders($mailbox);

        $this->assertNotEmpty($result, 'Cannot create default folders!');
        $this->assertTrue(is_array($result), 'Created folders are not in array!');
        $this->assertCount(4, $result);
    }
}
