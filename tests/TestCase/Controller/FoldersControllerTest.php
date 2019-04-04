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
        'plugin.messaging_center.folders'
    ];

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
}
