<?php
namespace MessagingCenter\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;
use MessagingCenter\Controller\FoldersController;

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
        'plugin.messaging_center.folders',
        'plugin.messaging_center.messages',
        'plugin.messaging_center.mailboxes',
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
     * Test add method
     *
     * @return void
     */
    public function testAdd() : void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/folders/add');

        $this->assertResponseOk();
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit() : void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/folders/edit/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete() : void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->post('/messaging-center/folders/delete/00000000-0000-0000-0000-000000000001');

        $this->assertRedirect();
    }

    /**
     * Test view action
     *
     * @return void
     */
    public function testView() : void
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
    public function testIndex() : void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/folders/index');

        $this->assertResponseOk();
    }
}
