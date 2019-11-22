<?php
namespace MessagingCenter\Test\TestCase\Controller;

use Cake\Datasource\EntityInterface;
use Cake\TestSuite\IntegrationTestCase;

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

    public function setUp(): void
    {
        parent::setUp();

        $this->enableRetainFlashMessages();
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);
    }

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->get('messaging-center/mailboxes');
        $this->assertResponseOk();
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/mailboxes/view/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertInstanceOf(EntityInterface::class, $this->viewVariable('mailbox'));
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/mailboxes/add');
        $this->assertResponseOk();

        $this->post('/messaging-center/mailboxes/add', [
            'user_id' => '00000000-0000-0000-0000-000000000002',
            'name' => 'New Mailbox',
            'type' => 'email',
            'incoming_transport' => 'internal',
            'IncomingSettings' => ['default'],
            'outgoing_transport' => 'internal',
            'OutgoingSettings' => ['default'],
            'active' => 1,
        ]);
        $this->assertRedirect(['action' => 'index']);
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get("/messaging-center/mailboxes/edit/$mailboxId");
        $this->assertResponseOk();

        $this->post("/messaging-center/mailboxes/edit/$mailboxId", [
            'name' => 'Edited Mailbox Name',
            'user_id' => '00000000-0000-0000-0000-000000000002',
            'type' => 'email',
            'incoming_transport' => 'internal',
            'IncomingSettings' => ['default'],
            'outgoing_transport' => 'internal',
            'OutgoingSettings' => ['default'],
            'active' => 1,
        ]);
        $this->assertRedirect(['action' => 'view', $mailboxId]);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->post('/messaging-center/mailboxes/delete/00000000-0000-0000-0000-000000000001');
        $this->assertRedirect(['action' => 'index']);
    }
}
