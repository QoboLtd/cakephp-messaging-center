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
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Entity\Mailbox;
use NinjaMutex\MutexException;
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
        $this->out('Fetching mail for [' . $mailbox->name . ']');
    }
}
