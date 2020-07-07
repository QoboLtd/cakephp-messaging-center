<?php
namespace MessagingCenter\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

/**
 * MessagingCenter\Controller\FoldersController Test Case
 */
class FoldersControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.MessagingCenter.Folders',
        'plugin.MessagingCenter.Messages',
        'plugin.MessagingCenter.Mailboxes',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->disableErrorHandlerMiddleware();

        $this->enableRetainFlashMessages();
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/folders/add');
        $this->assertResponseOk();

        $this->post('/messaging-center/folders/add', [
            'name' => 'Test',
            'type' => 'system',
            'mailbox_id' => '00000000-0000-0000-0000-000000000001',
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
        $folderId = '00000000-0000-0000-0000-000000000001';
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get("/messaging-center/folders/edit/$folderId");
        $this->assertResponseOk();

        $this->post("/messaging-center/folders/edit/$folderId", [
            'name' => 'New Folder Name',
        ]);
        $this->assertRedirect(['action' => 'view', $folderId]);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->post('/messaging-center/folders/delete/00000000-0000-0000-0000-000000000001');
        $this->assertRedirect(['action' => 'index']);
    }

    /**
     * Test view action
     *
     * @return void
     */
    public function testView(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/folders/view/00000000-0000-0000-0000-000000000001');
        $this->assertResponseOk();
    }

    /**
     * Test index action
     *
     * @return void
     */
    public function testIndex(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/folders/index');
        $this->assertResponseOk();
    }
}
