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
use Cake\ORM\TableRegistry;
use InvalidArgumentException;
use MessagingCenter\Enum\IncomingTransportType;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Entity\Mailbox;
use NinjaMutex\MutexException;
use PhpImap\ConnectionException;
use PhpImap\Mailbox as RemoteMailbox;
use Qobo\Utils\Utility\Lock\FileLock;

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

        /** @var \MessagingCenter\Model\Table\MailboxesTable $table */
        $table = TableRegistry::get('MessagingCenter.Mailboxes');
        $query = $table->findAllByTypeAndActive((string)MailboxType::EMAIL(), true);
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
            $this->out('Fetching mail for [' . $mailbox->name . ']');

            $settings = json_decode($mailbox->incoming_settings, true) ?? [];
            $settings = array_merge($defaultSettings, $settings);

            $connectionString = $this->getConnectionString($mailbox->incoming_transport, $settings);
            $this->out("Connection: $connectionString");
            $mailbox = new RemoteMailbox($connectionString, $settings['username'], $settings['password']);

            $messageIds = $mailbox->searchMailbox('ALL');
            if (empty($messageIds)) {
                return;
            }

            foreach ($messageIds as $messageId) {
                $message = $mailbox->getMail($messageId);
                debug($message);

                return;
            }
        } catch (InvalidArgumentException $e) {
            $this->warn($e->getMessage());
        } catch (ConnectionException $e) {
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
                $result .= '/notls';
                $result .= '}';
                // TODO: Make this flexible
                $result .= 'INBOX';

                break;
            default:
                throw new InvalidArgumentException("Incoming transport type [$type] is not supported");
        }

        return $result;
    }
}
