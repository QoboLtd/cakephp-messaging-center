<?php

namespace MessagingCenter\Model;

use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\ORM\TableRegistry;
use DateTime;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\Entity\Message;
use MessagingCenter\Model\Table\MailboxesTable;
use MessagingCenter\Model\Table\MessagesTable;
use PhpImap\IncomingMail;
use Qobo\Utils\Utility\Convert;
use Webmozart\Assert\Assert;

class MessageFactory
{
    /**
     * Creates and saves a new Message, under the specified Mailbox.
     *
     * @param \PhpImap\IncomingMail $incomingMail Incoming email to be saved as Message
     * @param \MessagingCenter\Model\Entity\Mailbox $mailbox Mailbox to hold this incoming message
     * @return \MessagingCenter\Model\Entity\Message
     */
    public static function fromIncomingMail(IncomingMail $incomingMail, Mailbox $mailbox): Message
    {
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);

        $messages = TableRegistry::getTableLocator()->get('MessagingCenter.Messages');
        Assert::isInstanceOf($messages, MessagesTable::class);

        $content = $incomingMail->textPlain ?? $incomingMail->textHtml;

        /**
         * Retrieve initialStatus for local saved email from configuration.
         * @TODO Put this into database while setting up mailbox
         * @var string
         */
        $initialStatus = (string)Configure::read('MessagingCenter.local_mailbox_messages.initialStatus', 'new');

        $headers = Convert::objectToArray($incomingMail->headers);

        $entity = $messages->newEntity([
            'subject' => $incomingMail->subject,
            'content' => $content,
            'status' => $initialStatus,
            'date_sent' => self::extractDateTime($incomingMail),
            'from_user' => '',
            'from_name' => '',
            'to_user' => '',
            'headers' => $headers,
            'message_id' => $incomingMail->messageId,
            'folder_id' => $mailboxes->getFolderByName($mailbox, MailboxesTable::FOLDER_INBOX),
        ]);

        $messageCreated = $messages->saveOrFail($entity);
        Assert::isInstanceOf($messageCreated, Message::class);

        self::saveAttachments($messageCreated, $incomingMail->getAttachments());

        return $messageCreated;
    }

    /**
     * Saves and links attachments with the specified message
     *
     * @param \Cake\Datasource\EntityInterface $message Message to be associated with attachmetns
     * @param \PhpImap\IncomingMailAttachment[] $attachments Attachments to be saved
     */
    protected static function saveAttachments(EntityInterface $message, array $attachments): void
    {
        $storageTable = TableRegistry::getTableLocator()->get('Burzum/FileStorage.FileStorage');
        foreach ($attachments as $attachment) {
            $storage = $storageTable->newEntity([
                'file' => [
                    'tmp_name' => $attachment->filePath,
                    'error' => 0,
                    'name' => $attachment->name,
                    'type' => null,
                    'size' => null,
                ]
            ]);

            $storage = $storageTable->patchEntity($storage, [
                'model' => 'Messages',
                'model_field' => 'attachments',
                'foreign_key' => $message->get('id'),
                'extension' => strtolower($storage->get('extension')),
            ]);

            $storageTable->saveOrFail($storage);
        }
    }

    /**
     * Extracts the DateTime from the provided message
     *
     * @param mixed $message Email message object
     * @return \DateTime
     */
    protected static function extractDateTime($message): DateTime
    {
        if (property_exists($message, 'udate')) {
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
