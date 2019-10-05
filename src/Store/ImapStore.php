<?php

namespace MessagingCenter\Store;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Exception\PersistenceFailedException;
use Exception;
use MessagingCenter\Message\MailMessage;
use MessagingCenter\Model\MessageFactory;
use PhpImap\Mailbox as RemoteMailbox;

class ImapStore implements StoreInterface
{
    use InstanceConfigTrait;

    const CRITERIA_NONE = 'ALL';

    protected $_defaultConfig = [
        'username' => '',
        'password' => '',
        'host' => 'localhost',
        'port' => 993,
        'protocol' => 'imap',
        'mailboxName' => 'INBOX',
        'markAsSeen' => false,
    ];

    /**
     * ImapStore constructor.
     * @param mixed[] $config Configuration
     */
    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    /**
     * @param int|null $limit How many messages to return
     * @param int $offset How many rows to skip before beginning to return messages
     * @return \MessagingCenter\Message\MessageInterface[]
     * @throws \PhpImap\Exceptions\InvalidParameterException
     */
    public function getMessages(int $limit = null, int $offset = 0): array
    {
        return $this->searchMessages([self::CRITERIA_NONE], $limit, $offset);
    }

    /**
     * @param string[] $criteria Search criteria
     * @param int|null $limit How many messages to return
     * @param int $offset How many rows to skip before beginning to return messages
     * @return \MessagingCenter\Message\MessageInterface[]
     */
    public function searchMessages(array $criteria, int $limit = null, int $offset = 0): array
    {
        $tmpDir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        $mailbox = new RemoteMailbox(
            $this->getConnectionString(),
            $this->getConfig('username'),
            $this->getConfig('password'),
            (string)$tmpDir
        );

        $messageIds = $this->searchMailbox($mailbox, (string)$criteria[0]);
        if (empty($messageIds)) {
            return [];
        }

        $messageIds = array_reverse($messageIds);
        if (!empty($limit)) {
            $messageIds = array_slice($messageIds, $offset, $limit);
        }

        $messages = [];
        $allMessageHeaders = $mailbox->getMailsInfo($messageIds);
        foreach ($allMessageHeaders as $messageHeader) {
            if (!property_exists($messageHeader, 'message_id') || !property_exists($messageHeader, 'uid')) {
                continue;
            }

            $markAsSeen = $this->getConfig('markAsSeen');
            $uid = $messageHeader->uid;
            $messages[] = new MailMessage($messageHeader->message_id, function () use ($uid, $mailbox, $markAsSeen) {
                return $mailbox->getMail($uid, $markAsSeen);
            });
        }

        return $messages;
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
     * Builds and returns the IMAP4 Connection String
     *
     * Example: {localhost:993/imap/notls}INBOX
     *
     * @return string
     */
    public function getConnectionString(): string
    {
        $connectionString = '';
        $connectionString .= '{';
        $connectionString .= $this->getConfig('host');
        $connectionString .= ':' . $this->getConfig('port');
        $connectionString .= '/' . $this->getConfig('protocol');
        // @todo Make this optional
        $connectionString .= '/ssl/novalidate-cert';
        $connectionString .= '}';
        // @todo Make this flexible
        $connectionString .= $this->getConfig('mailboxName');

        return $connectionString;
    }
}
