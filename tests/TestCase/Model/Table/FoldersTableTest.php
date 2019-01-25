<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MessagingCenter\Model\Table\FoldersTable;

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
        'plugin.messaging_center.folders',
        'plugin.messaging_center.mailboxes'
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
    public function testInitialize() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
