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
use Cake\Datasource\EntityInterface;
use Cake\ORM\Exception\PersistenceFailedException;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use DateTime;
use Exception;
use InvalidArgumentException;
use MessagingCenter\Enum\IncomingTransportType;
use MessagingCenter\Enum\MailboxType;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Model\Table\MessagesTable;
use NinjaMutex\MutexException;
use PhpImap\Exceptions\ConnectionException;
use PhpImap\Exceptions\InvalidParameterException;
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

        $table = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($table, MailboxesTable::class);

        /**
         * @var \Cake\ORM\Query $query
         */
        $query = $table->find()
            ->where([
                'type' => (string)MailboxType::EMAIL(),
                'active' => true,
            ])
            ->contain(['Folders']);
        Assert::isInstanceOf($query, Query::class);

        $limit = empty($this->params['limit']) ? null : (int)$this->params['limit'];
        $since = null;

        if (!empty($this->params['since'])) {
            $time = strtotime($this->params['since']);
            if ($time !== false) {
                $since = (int)$time;
            }
        }

        foreach ($query->all() as $mailbox) {
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
        $defaultSettings = [
            'username' => '',
            'password' => '',
            'host' => 'localhost',
            'port' => null,
            'protocol' => 'imap',
        ];

        // SINCE 01-Jan-2000
        $search_criteria = empty($since) ? 'ALL' : 'SINCE ' . date('d-M-Y', $since);

        /**
         * Retrieve mark as seen remote email status from configuration.
         * @TODO Put this into database while setting up mailbox
         * @var bool
         */
        $markAsSeenRemote = (bool)Configure::read('MessagingCenter.remote_mailbox_messages.markAsSeen', true);

        try {
            $this->out('Fetching mail for [' . $mailbox->get('name') . ']');

            $settings = json_decode($mailbox->get('incoming_settings'), true) ?? [];
            $settings = array_merge($defaultSettings, $settings);

            $connectionString = $this->getConnectionString($mailbox->get('incoming_transport'), $settings);

            $this->out("Connection: $connectionString; username=" . $settings['username'] . "; password=" . $settings['password']);

            $remoteMailbox = new RemoteMailbox($connectionString, $settings['username'], $settings['password']);

            $remoteMailbox->setAttachmentsIgnore(true);
            $messageIds = $this->searchMailbox($remoteMailbox, $search_criteria);
            if (empty($messageIds)) {
                $this->out("Mailbox is empty");

                return;
            }
        } catch (InvalidArgumentException | InvalidParameterException | ConnectionException $e) {
            $this->abort($e->getMessage());

            return;
        }

        $this->out('Fetching headers');

        $messageIds = array_reverse($messageIds);
        if (!empty($limit)) {
            $messageIds = array_slice($messageIds, 0, $limit);
        }

        $allMessageHeaders = $remoteMailbox->getMailsInfo($messageIds);
        foreach ($allMessageHeaders as $messageHeader) {
            if (!property_exists($messageHeader, 'message_id') || !property_exists($messageHeader, 'uid')) {
                $this->err('Message ID / UID is missing');

                continue;
            }

            $messageId = trim($messageHeader->message_id);
            if ($this->hasMessage($messageId, $mailbox)) {
                $this->out(sprintf('Message %s already exists and it was skipped', $messageId));

                continue;
            }

            try {
                /** @var \PhpImap\IncomingMail $message */
                $message = $remoteMailbox->getMail($messageHeader->uid, $markAsSeenRemote);

                $this->saveMessage($message, $mailbox);
                $this->out(sprintf('Message %s saved', trim($messageHeader->message_id)));
            } catch (InvalidArgumentException | PersistenceFailedException $e) {
                $this->err(sprintf('Message %s can not be saved. %s', trim($messageHeader->message_id), $e->getMessage()));
            }
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
    protected function getConnectionString(string $type, array $settings) : string
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
     * Search the mailbox provided and returns an array including the message ids.
     *
     * It also handles intricacies for Office 365 / Exchange servers
     *
     * @link https://github.com/barbushin/php-imap/issues/101#issuecomment-378136507
     * @param \PhpImap\Mailbox $remoteMailbox Remote mailbox to access and search
     * @param string $criteria Criteria to be used when searching the mailbox
     * @return mixed[]
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    protected function searchMailbox(RemoteMailbox $remoteMailbox, string $criteria): array
    {
        try {
            return $remoteMailbox->searchMailbox($criteria);
        } catch (Exception $e) {
            // Ugly hack to catch only the BADCHASET cases and retry with server encoding disabled
            if (strpos($e->getMessage(), 'BADCHARSET') !== false) {
                return $remoteMailbox->searchMailbox($criteria, true);
            }

            throw $e;
        }
    }

    /**
     * Saves the message into the database, under the specified mailbox.
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

        $content = $message->textPlain ?? $message->textHtml;

        /**
         * Retrieve initialStatus for local saved email from configuration.
         * @TODO Put this into database while setting up mailbox
         * @var string
         */
        $initialStatus = (string)Configure::read('MessagingCenter.local_mailbox_messages.initialStatus', 'new');

        $entity = $table->newEntity();
        $table->patchEntity($entity, [
            'subject' => $message->subject,
            'content' => $content,
            'status' => $initialStatus,
            'date_sent' => $this->extractDateTime($message),
            'from_user' => '',
            'from_name' => '',
            'to_user' => '',
            'headers' => $message->headers,
            'message_id' => $message->messageId,
            'folder_id' => $mailboxes->getInboxFolder($mailbox),
        ]);

        $table->saveOrFail($entity);
    }

    /**
     * Returns true only and only if the message provided already exists in database.
     *
     * @param string $messageId Message ID received from the mailbox
     * @param \Cake\Datasource\EntityInterface $mailbox to save message
     * @return bool
     */
    protected function hasMessage(string $messageId, EntityInterface $mailbox): bool
    {
        $table = TableRegistry::getTableLocator()->get('MessagingCenter.Messages');
        Assert::isInstanceOf($table, MessagesTable::class);

        $mailboxId = $mailbox->get('id');
        $query = $table->find()
            ->where([
                'message_id' => $messageId
            ])
            ->contain([
                'Folders' => function ($q) use ($mailboxId) {
                    return $q->where(['mailbox_id' => $mailboxId]);
                }
            ]);
        Assert::isInstanceOf($query, Query::class);
        $result = $query->count();

        return ($result > 0);
    }

    /**
     * Extracts the DateTime from the provided message
     *
     * @param mixed $message Email message object
     * @return \DateTime
     */
    protected function extractDateTime($message): DateTime
    {
        if (!empty($message->udate)) {
            $dateSent = new DateTime($message->udate);
        } else {
            $dateSent = DateTime::createFromFormat('Y-m-d H:i:s', $message->date);
        }

        $errors = DateTime::getLastErrors();
        if ($dateSent !== false && empty($errors['warning_count'])) {
            return $dateSent;
        }

        return new DateTime();
    }
}
