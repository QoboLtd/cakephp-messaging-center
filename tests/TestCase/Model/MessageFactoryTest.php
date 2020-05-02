<?php

namespace MessagingCenter\Test\TestCase\Model;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\MessageFactory;
use PhpImap\DataPartInfo;
use PhpImap\IncomingMail;
use Webmozart\Assert\Assert;

class MessageFactoryTest extends TestCase
{
    public $fixtures = [
        'plugin.CakeDC/Users.Users',
        'plugin.MessagingCenter.Messages',
        'plugin.MessagingCenter.Folders',
        'plugin.MessagingCenter.Mailboxes',
    ];

    public function testCreateFromIncomingMail(): void
    {
        $mailboxesTable = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        $mailbox = $mailboxesTable->get('00000000-0000-0000-0000-000000000001');
        Assert::isInstanceOf($mailbox, Mailbox::class);

        $data = [
            'subject' => 'Hello world',
            'date' => '2019-01-01 01:01:01',
        ];

        $incomingMail = new IncomingMail();
        foreach ($data as $key => $value) {
            $incomingMail->{$key} = $value;
        }

        $incomingMailbox = new \PhpImap\Mailbox('', '', '');
        $incomingMail->addDataPartInfo(
            new DataPartInfoMock(),
            DataPartInfo::TEXT_PLAIN
        );

        $message = MessageFactory::fromIncomingMail($incomingMail, $mailbox);
        $this->assertEquals($data['subject'], $message->get('subject'));

        /** @var \DateTime $date_sent */
        $dateSent = $message->get('date_sent');
        $this->assertEquals($data['date'], $dateSent->format('Y-m-d H:i:s'));
    }
}
