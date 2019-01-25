<?php
namespace MessagingCenter\Test\TestCase\Model\Table;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
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

        Configure::write('MessagingCenter.Mailbox.default', [
            'mailbox_type' => 'system',
            'incoming_transport' => 'internal',
            'incoming_settings' => 'default',
            'outgoing_transport' => 'internal',
            'outgoing_settings' => 'default',
            'mailbox_postfix' => '@system',
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
}
