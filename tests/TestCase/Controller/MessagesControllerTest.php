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
        $this->get('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000001');

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

    public function testComposeMustFailForNonSystemMailboxes(): void
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
        $this->post('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000001');

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
        $this->post('/messaging-center/messages/compose/00000000-0000-0000-0000-000000000001', $data);

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

    public function testReplyPost(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $entity = $this->MessagesTable->get($id, [
            'contain' => ['Folders', 'ToUser', 'FromUser']
        ]);

        $expected = 2 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message ' . rand(),
            'content' => 'Bla bla bla' . rand(),
            'content_text' => 'Bla bla bla' . rand(),
            'to_user' => $entity->get('from_user')
        ];

        $this->post('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);

        $url = [
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            $mailboxId
        ];
        $this->assertRedirect($url);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->where(['subject' => $data['subject']]);
        $this->assertEquals(2, $query->count());
        /**
         * @var \MessagingCenter\Model\Entity\Message $newEntity
         */
        $newEntity = $query->first();
        $this->assertEquals($entity->get('from_user'), $newEntity->get('to_user'));
        $this->assertEquals($data['content'], $newEntity->get('content'));
        $this->assertEquals($data['content_text'], $newEntity->get('content_text'));
        /**
         * @var \Cake\Http\Session $session
         */
        $session = $this->_requestSession;
        $this->assertEquals($session->read('Auth.User.id'), $newEntity->from_user);
        $this->assertEquals('new', $newEntity->status);
        $time = new Time();
        $this->assertEquals($time->i18nFormat(), $newEntity->date_sent->i18nFormat());
        // verify existing message was not affected.
        $entityVerify = $this->MessagesTable->get($id, [
            'contain' => ['Folders', 'ToUser', 'FromUser']
        ]);
        $this->assertEquals($entity->toArray(), $entityVerify->toArray());
        $this->assertSession('The message has been sent.', 'Flash.flash.0.message');
    }

    public function testReplyMustFailForNonSystemMailboxes(): void
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

    public function testReplyPostSameUser(): void
    {
        $this->markTestSkipped('Skip this test till refactoring will be completed');

        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';

        $expected = $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testReplyPut message',
            'content' => 'Bla bla bla'
        ];
        $this->post('/messaging-center/messages/reply/' . $id, $data);

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

    public function testReplyPostNoData(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $expected = $this->MessagesTable->find('all')->count();
        $this->post('/messaging-center/messages/reply/' . $id, []);

        $this->assertResponseCode(200);
        $this->assertSession('The message could not be sent. Please, try again.', 'Flash.flash.0.message');
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());
    }

    public function testReplyPostEnforceData(): void
    {
        $id = '00000000-0000-0000-0000-000000000001';
        $mailboxId = '00000000-0000-0000-0000-000000000001';
        $expected = 2 + $this->MessagesTable->find('all')->count();

        $data = [
            'subject' => 'testComposePost message ' . rand(),
            'content' => 'Bla bla bla',
            // try to enforce protected data
            'to_user' => 'Enforce other user id',
            'from_user' => 'Enforce other user id',
            'status' => 'Enforce custom message status',
            'date_sent' => 'Enforce custom date sent',
        ];
        $this->post('/messaging-center/messages/reply/' . $id, $data);

        $this->assertResponseCode(302);
        $this->assertEquals($expected, $this->MessagesTable->find('all')->count());

        $query = $this->MessagesTable->find()->limit(1)->where(['subject' => $data['subject'], 'id <> ' > $id]);

        /**
         * @var \MessagingCenter\Model\Entity\Message $entity
         */
        $entity = $query->first();
        $this->assertNotEquals($data['to_user'], $entity->to_user);
        $this->assertNotEquals($data['from_user'], $entity->from_user);
        $this->assertNotEquals($data['status'], $entity->status);
        $this->assertNotEquals($data['date_sent'], $entity->date_sent);

        $this->assertSession('The message has been sent.', 'Flash.flash.0.message');
    }

    /**
     * @dataProvider deleteDataProvider
     */
    public function testDelete(string $id): void
    {
        $trashFolderId = '00000000-0000-0000-0000-000000000008';

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);
        $this->assertRedirect([
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            '00000000-0000-0000-0000-000000000001',
            $trashFolderId,
        ]);

        $this->assertSame($trashFolderId, $this->MessagesTable->get($id)->get('folder_id'));
    }

    /**
     * @return array[]
     */
    public function deleteDataProvider(): array
    {
        return [
            // Delete from Inbox
            ['00000000-0000-0000-0000-000000000001'],
            // Delete already Deleted
            ['00000000-0000-0000-0000-000000000012'],
            // Delete from Archive
            ['00000000-0000-0000-0000-000000000013'],
            // Delete from Sent
            ['00000000-0000-0000-0000-000000000006'],
        ];
    }

    public function testDeleteMustFailForNonSystemMailboxes(): void
    {
        $id = '00000000-0000-0000-0000-000000000007';
        $expected = $this->MessagesTable->get($id)->toArray();

        $this->delete('/messaging-center/messages/delete/' . $id);

        $this->assertResponseCode(302);
        $this->assertEquals($expected, $this->MessagesTable->get($id)->toArray());
    }

    /**
     * @dataProvider archiveDataProvider
     */
    public function testArchive(string $id): void
    {
        $archiveFolderId = '00000000-0000-0000-0000-000000000009';

        $this->post('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);
        $this->assertRedirect([
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            '00000000-0000-0000-0000-000000000001',
            $archiveFolderId,
        ]);

        $this->assertSame($archiveFolderId, $this->MessagesTable->get($id)->get('folder_id'));
    }

    /**
     * @return array[]
     */
    public function archiveDataProvider(): array
    {
        return [
            // Archive from Inbox
            ['00000000-0000-0000-0000-000000000001'],
            // Archive from Delete
            ['00000000-0000-0000-0000-000000000012'],
            // Archive already Archived
            ['00000000-0000-0000-0000-000000000013'],
            // Archive from Sent
            ['00000000-0000-0000-0000-000000000006'],
        ];
    }

    public function testArchiveMustFailForNonSystemMailboxes(): void
    {
        $id = '00000000-0000-0000-0000-000000000007';
        $expected = $this->MessagesTable->get($id)->toArray();

        $this->delete('/messaging-center/messages/archive/' . $id);

        $this->assertResponseCode(302);
        $this->assertEquals($expected, $this->MessagesTable->get($id)->toArray());
    }

    /**
     * @dataProvider restoreDataProvider
     */
    public function testRestore(string $id): void
    {
        $inboxFolderId = '00000000-0000-0000-0000-000000000002';

        $this->post('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);
        $this->assertRedirect([
            'plugin' => 'MessagingCenter',
            'controller' => 'Mailboxes',
            'action' => 'view',
            '00000000-0000-0000-0000-000000000001',
            $inboxFolderId,
        ]);

        $this->assertSame($inboxFolderId, $this->MessagesTable->get($id)->get('folder_id'));
    }

    /**
     * @return string[][]
     */
    public function restoreDataProvider(): array
    {
        return [
            // Restore already on Inbox
            ['00000000-0000-0000-0000-000000000001'],
            // Restore from Delete
            ['00000000-0000-0000-0000-000000000012'],
            // Restore from Archive
            ['00000000-0000-0000-0000-000000000013'],
            // Restore from Sent
            ['00000000-0000-0000-0000-000000000006'],
        ];
    }

    public function testRestoreMustFailForNonSystemMailboxes(): void
    {
        $id = '00000000-0000-0000-0000-000000000007';
        $expected = $this->MessagesTable->get($id)->toArray();

        $this->delete('/messaging-center/messages/restore/' . $id);

        $this->assertResponseCode(302);
        $this->assertEquals($expected, $this->MessagesTable->get($id)->toArray());
    }
}
