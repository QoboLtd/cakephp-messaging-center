<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace MessagingCenter\Shell;

use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;
use MessagingCenter\Enum\IncomingTransportType;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Model\Table\MessagesTable;
use NinjaMutex\MutexException;
use PhpImap\ConnectionException;
use PhpImap\IncomingMail;
use PhpImap\Mailbox as RemoteMailbox;
use Qobo\Utils\Utility\Lock\FileLock;
use Webmozart\Assert\Assert;

class FetchMailShell extends Shell
{
    /**
     * Set shell description and command line options
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionsParser(): ConsoleOptionParser
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Fetch mail');

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @return void
     */
    public function main(): void
    {
        try {
            $lock = new FileLock('fetchmail_' . md5(__FILE__) . '.lock');
        } catch (MutexException $e) {
            $this->warn($e->getMessage());

            return;
        }

        if (!$lock->lock()) {
            $this->warn('Fetching mail is already in progress');

            return;
        }

        $table = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($table, MailboxesTable::class);

        /**
         * @var \Cake\ORM\Query $query
         */
        $query = $table->find()
            ->contain('Folders')
            ->where([
                'type' => (string)MailboxType::EMAIL(),
                'active' => true,
            ]);

        foreach ($query->all() as $mailbox) {
            $this->processMailbox($mailbox);
        }
    }

    /**
     * Fetch mail for a given mailbox
     *
     * @param \MessagingCenter\Model\Entity\Mailbox $mailbox Mailbox instance
     * @return void
     */
    protected function processMailbox(Mailbox $mailbox): void
    {
        $defaultSettings = [
            'username' => '',
            'password' => '',
            'host' => 'localhost',
            'port' => null,
            'protocol' => 'imap',
        ];

        try {
            $this->out('Fetching mail for [' . $mailbox->get('name') . ']');

            $settings = json_decode($mailbox->get('incoming_settings'), true) ?? [];
            $settings = array_merge($defaultSettings, $settings);

            $connectionString = $this->getConnectionString($mailbox->get('incoming_transport'), $settings);

            $this->out("Connection: $connectionString; username=" . $settings['username'] . "; password=" . $settings['password']);

            $remoteMailbox = new RemoteMailbox($connectionString, $settings['username'], $settings['password']);

            $messageIds = $remoteMailbox->searchMailbox('ALL');
            if (empty($messageIds)) {
                $this->out("Mailbox is empty");

                return;
            }

            foreach ($messageIds as $messageId) {
                /** @var \PhpImap\IncomingMail $message */
                $message = $remoteMailbox->getMail($messageId);

                $this->saveMessage($message, $mailbox);
            }
        } catch (InvalidArgumentException | ConnectionException $e) {
            $this->warn($e->getMessage());
        }
    }

    /**
     * Build connection string
     *
     * Example: {localhost:993/imap/notls}INBOX
     *
     * @param string $type Incoming transport type
     * @param mixed[] $settings Incoming transport settings
     * @return string
     */
    protected function getConnectionString(string $type, array $settings): string
    {
        $result = '';

        switch ($type) {
            case (string)IncomingTransportType::IMAP4():
                // See more details at http://php.net/manual/en/function.imap-open.php
                $result .= '{';
                $result .= $settings['host'] ?? 'localhost';
                $result .= ':' . ($settings['port'] ?? 993);
                $result .= '/' . ($settings['protocol'] ?? 'imap');
                // TODO: Make this optional
                $result .= '/ssl/novalidate-cert';
                $result .= '}';
                // TODO: Make this flexible
                $result .= 'INBOX';

                break;
            default:
                throw new InvalidArgumentException("Incoming transport type [$type] is not supported");
        }

        return $result;
    }

    /**
     * saveMessage method
     *
     * @param \PhpImap\IncomingMail $message received from the mailbox
     * @param \Cake\Datasource\EntityInterface $mailbox to save message
     * @return void
     */
    protected function saveMessage(IncomingMail $message, EntityInterface $mailbox) : void
    {
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);

        $table = TableRegistry::getTableLocator()->get('MessagingCenter.Messages');
        Assert::isInstanceOf($table, MessagesTable::class);

        $result = $table->findByMessageId($message->messageId)
            ->count();

        if ($result > 0) {
            return;
        }

        $entity = $table->newEntity();
        $table->patchEntity($entity, [
            'subject' => $message->subject,
            'content' => $message->textHtml,
            'status' => 'new',
            'from_user' => $message->fromAddress,
            'from_name' => $message->fromName,
            'to_user' => $message->toString,
            'message_id' => $message->messageId,
            'folder_id' => $mailboxes->getInboxFolder($mailbox),
        ]);

        $result = $table->save($entity);
    }
}
