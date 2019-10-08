<?php
namespace MessagingCenter\Test\TestCase\Controller;

use Cake\Event\EventManager;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;
use MessagingCenter\Event\Model\MailboxListener;
use MessagingCenter\Event\Model\UserListener;
use MessagingCenter\Model\Entity\Message;
use MessagingCenter\Model\Table\MessagesTable;

/**
 * MessagingCenter\Controller\MessagesController Test Case
 */
class MessagesControllerTest extends IntegrationTestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.CakeDC/Users.users',
        'plugin.messaging_center.messages',
        'plugin.messaging_center.folders',
        'plugin.messaging_center.mailboxes',
        'plugin.Burzum/FileStorage.FileStorage'
    ];

    /**
     * @var \MessagingCenter\Model\Table\MessagesTable $MessagesTable
     */
    protected $MessagesTable;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * @var \MessagingCenter\Model\Table\MessagesTable $table
         */
        $table = TableRegistry::get('MessagingCenter.Messages');
        $this->MessagesTable = $table;

        $this->enableRetainFlashMessages();
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        EventManager::instance()->on(new UserListener());
        EventManager::instance()->on(new MailboxListener());
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MessagesTable);

        parent::tearDown();
    }

    public function testViewInboxMessage(): void
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000001');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewSentMessage(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000002']);

        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewDeletedMessage(): void
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewArchivedMessage(): void
    {
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000003');

        $this->assertResponseOk();
        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
    }

    public function testViewOtherUserMessages(): void
    {
        $this->session(['Auth.User.id' => '00000000-0000-0000-0000-000000000001']);
        $this->get('/messaging-center/messages/view/00000000-0000-0000-0000-000000000004');

        $this->assertResponseCode(200);
    }

    public function testCompose(): void
    {
        $this->get('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();

        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
        $this->assertTrue($this->viewVariable('message')->isNew());
    }

    public function testComposePost(): void
    {
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $expected = 2 + $this->MessagesTable->find('all')->count();

        $data = [
            'to_user' => '00000000-0000-0000-0000-000000000002',
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla'
        ];
        $this->post('/messaging-center/messages/compose/' . $mailboxId, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertEquals($data['to_user'], $entity->to_user);
        $this->assertEquals($data['content'], $entity->content);
        /**
         * @var \Cake\Http\Session $session
         */
        $session = $this->_requestSession;
        $this->assertEquals($session->read('Auth.User.id'), $entity->from_user);
        $this->assertEquals('new', $entity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $entity->date_sent->i18nFormat(), '', 1);
    }

    public function testComposeShouldFailForNonInternalMailboxes(): void
    {
        $expected = $this->MessagesTable->find('all')->count();

        $mailboxId = '00000000-0000-0000-0000-000000000002';
        $this->get('/messaging-center/messages/compose/' . $mailboxId);
        $this->assertResponseCode(302);

        $this->post('/messaging-center/messages/compose/' . $mailboxId, [
            'to_user' => '00000000-0000-0000-0000-000000000002',
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla'
        ]);
        $this->assertResponseCode(302);

        $this->assertSame($expected, $this->MessagesTable->find('all')->count());
    }

    public function testComposePostNoData(): void
    {
        $expected = $this->MessagesTable->find('all')->count();
        $this->post('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000002');

        $this->assertResponseOk();
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testComposePostEnforceData(): void
    {
        $expected = 2 + $this->MessagesTable->find('all')->count();

        $data = [
            'to_user' => '00000000-0000-0000-0000-000000000001',
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla',
            // try to enforce protected data
            'from_user' => 'Enforce other user id',
            'status' => 'Enforce custom message status',
            'date_sent' => 'Enforce custom date sent',
        ];
        $this->post('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000002', $data);

        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);
    }

    public function testReply(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $this->get('/messaging-center/messages/reply/' . $id);

        $this->assertResponseOk();

        $this->assertInstanceOf(Message::class, $this->viewVariable('message'));
        $this->assertFalse($this->viewVariable('message')->isNew());
    }

    public function testReplyPut(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $entity = $this->MessagesTable->get($id);

        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $newEntity
         */
        $newEntity = $query->first();
        $this->assertEquals($entity->get('from_user'), $newEntity->to_user);
        $this->assertEquals($data['content'], $newEntity->content);
        /**
         * @var \Cake\Http\Session $session
         */
        $session = $this->_requestSession;
        $this->assertEquals($session->read('Auth.User.id'), $newEntity->from_user);
        $this->assertEquals('new', $newEntity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $newEntity->date_sent->i18nFormat());
        // verify existing message was not affected.
        $this->assertEquals($entity->toArray(), $this->MessagesTable->get($id)->toArray());
    }

    public function testReplyShouldFailForNonInternalMailboxes(): void
    {
        $expected = $this->MessagesTable->find('all')->count();

        $messageId = '00000000-0000-0000-0000-000000000007';
        $this->get('/messaging-center/messages/reply/' . $messageId);
        $this->assertResponseCode(302);

        $this->put('/messaging-center/messages/reply/' . $messageId, [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ]);
        $this->assertResponseCode(302);

        $this->assertSame($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutSameUser(): void
    {
        $this->markTestSkipped('Skip this test till refactoring will be completed');

        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';

        $expected = $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutNoData(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $expected = $this->MessagesTable->find('all')->count();
        $this->put('/messaging-center/messages/reply/' . $id);

        $this->assertResponseOk();
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPutEnforceData(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $expected = 1 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testComposePost message',
            'content' => 'Bla bla bla',
            // try to enforce protected data
            'to_user' => 'Enforce other user id',
            'from_user' => 'Enforce other user id',
            'status' => 'Enforce custom message status',
            'date_sent' => 'Enforce custom date sent',
        ];
        $this->put('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject']]);
        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertNotEquals($data['to_user'], $entity->to_user);
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);
    }

    /**
     * @dataProvider deleteDataProvider
     */
    public function testDelete(string $id): void
    {
        $mailboxId = '00000000-0000-0000-0000-000000000002';
        $folderId = '00000000-0000-0000-0000-000000000006';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId,
            $folderId,
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals($folderId, $entity->get('folder_id'));
    }

    /**
     * @return array[]
     */
    public function deleteDataProvider(): array
    {
        return [
            // Delete from Inbox
            ['00000000-0000-0000-0000-000000000007'],
            // Delete already Deleted
            ['00000000-0000-0000-0000-000000000008'],
            // Delete from Sent
            ['00000000-0000-0000-0000-000000000009'],
        ];
    }

    /**
     * @dataProvider archiveDataProvider
     */
    public function testArchive(string $id): void
    {
        $mailboxId = '00000000-0000-0000-0000-000000000002';
        $folderId = '00000000-0000-0000-0000-000000000007';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId,
            $folderId,
        ];
        $this->assertRedirect($url);

        $entity = $this->MessagesTable->get($id);
        $this->assertEquals($folderId, $entity->get('folder_id'));
    }

    /**
     * @return array[]
     */
    public function archiveDataProvider(): array
    {
        return [
            // Archive from Inbox
            ['00000000-0000-0000-0000-000000000007'],
            // Delete already Archived
            ['00000000-0000-0000-0000-000000000010'],
            // Archive from Sent
            ['00000000-0000-0000-0000-000000000009'],
        ];
    }
}
