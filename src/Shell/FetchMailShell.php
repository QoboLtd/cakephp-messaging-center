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
use Cake\Core\Configure;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Message\MailMessage;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\MessageFactory;
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Store\ImapStore;
use NinjaMutex\MutexException;
use Qobo\Utils\Utility\Lock\FileLock;
use Webmozart\Assert\Assert;

class FetchMailShell extends Shell
{
    /**
     * Set shell description and command line options
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser(): ConsoleOptionParser
    {
        $parser = new ConsoleOptionParser('console');
        $parser->setDescription('Fetch mail');

        $parser->addOption('since', ['short' => 's', 'help' => 'Fetch emails received since a particular date', 'default' => null]);
        $parser->addOption('limit', ['short' => 'l', 'help' => 'Limit number of emails to fetch', 'default' => null]);

        return $parser;
    }

    /**
     * Main method for shell execution
     *
     * @return bool|int|null
     */
    public function main()
    {
        try {
            $lock = new FileLock('fetchmail_' . md5(__FILE__) . '.lock');
        } catch (MutexException $e) {
            $this->warn($e->getMessage());

            return null;
        }

        if (!$lock->lock()) {
            $this->warn('Fetching mail is already in progress');

            return null;
        }

        /** @var MailboxesTable $table */
        $table = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($table, MailboxesTable::class);

        $limit = empty($this->params['limit']) ? null : (int)$this->params['limit'];
        $since = null;

        if (!empty($this->params['since'])) {
            $time = strtotime($this->params['since']);
            if ($time !== false) {
                $since = (int)$time;
            }
        }

        $activeMailboxes = $table->getActiveMailboxes((string)MailboxType::EMAIL());
        foreach ($activeMailboxes as $mailbox) {
            $this->processMailbox($mailbox, $since, $limit);
        }
    }

    /**
     * Fetch mail for a given mailbox
     *
     * @param \MessagingCenter\Model\Entity\Mailbox $mailbox Mailbox instance
     * @param int|null $since Timestamp to be used in since search criteria
     * @param int|null $limit How many emails to fetch
     * @return void
     */
    protected function processMailbox(Mailbox $mailbox, ?int $since, ?int $limit) : void
    {
        // SINCE 01-Jan-2000
        $searchCriteria = empty($since) ? 'ALL' : 'SINCE ' . date('d-M-Y', $since);

        /**
         * Retrieve mark as seen remote email status from configuration.
         * @TODO Put this into database while setting up mailbox
         * @var bool
         */
        $markAsSeenRemote = (bool)Configure::read('MessagingCenter.remote_mailbox_messages.markAsSeen', true);

        /** @var \MessagingCenter\Model\Table\MailboxesTable $mailboxesTable */
        $mailboxesTable = TableRegistry::getTableLocator()->get($mailbox->getSource());

        $this->out('Fetching mail for [' . $mailbox->get('name') . ']');

        $settings = $mailbox->get('incoming_settings');
        $settings['markAsSeen'] = $markAsSeenRemote;
        $imapStore = new ImapStore($settings);

        $this->out(sprintf(
            "Connection: %s; username=%s; password=%s",
            $imapStore->getConnectionString(),
            $imapStore->getConfig('username'),
            $imapStore->getConfig('password')
        ));

        $messages = $imapStore->searchMessages([$searchCriteria], $limit);
        foreach ($messages as $message) {
            if ($mailboxesTable->hasMessage($mailbox, $message->getUniqueId())) {
                $this->out(sprintf('Message %s already exists and it was skipped', $message->getUniqueId()));

                continue;
            }

            try {
                // @todo add a method to return the message type so that factory can call the right factory method
                Assert::isInstanceOf($message, MailMessage::class);
                MessageFactory::fromIncomingMail($message->getIncomingMail(), $mailbox);
                $this->out(sprintf('Message %s saved', $message->getUniqueId()));
            } catch (InvalidArgumentException | PersistenceFailedException $e) {
                $this->err(sprintf('Message %s can not be saved. %s', $message->getUniqueId(), $e->getMessage()));
            }
        }
    }
}
