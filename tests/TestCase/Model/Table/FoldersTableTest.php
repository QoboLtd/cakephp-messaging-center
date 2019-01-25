<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
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
        'plugin.messaging_center.mailboxes',
        'plugin.messaging_center.folders',
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
     * test create default folders
     *
     * @return void
     */
    public function testCreateDefaultFolders() : void
    {
        $config = TableRegistry::getTableLocator()->exists('Mailboxes') ? [] : ['className' => MailboxesTable::class];
        $mailboxTable = TableRegistry::getTableLocator()->get('Mailboxes', $config);
        $mailbox = $mailboxTable->get('a62a4c06-6bc6-4660-a59c-51fe8d7e54ed');

        $result = $this->Folders->createDefaultFolders($mailbox);

        $this->assertNotEmpty($result, 'Cannot create default folders!');
        $this->assertTrue(is_array($result), 'Created folders are not in array!');
    }
}
