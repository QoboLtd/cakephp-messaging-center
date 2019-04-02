<?php
namespace MessagingCenter\Test\TestCase\Controller;

use Cake\Datasource\EntityInterface;
use Cake\TestSuite\IntegrationTestCase;
use MessagingCenter\Controller\MailboxesController;

/**
 * MessagingCenter\Controller\MailboxesController Test Case
 */
class MailboxesControllerTest extends IntegrationTestCase
{

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

    public function setUp() : void
    {
        parent::setUp();

        $this->enableRetainFlashMessages();
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex() : void
    {
        $this->get('messaging-center/mailboxes');
        $this->assertResponseOk();
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView() : void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('messaging-center/mailboxes/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
        $this->assertInstanceOf(EntityInterface::class, $this->viewVariable('mailbox'));
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete() : void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
