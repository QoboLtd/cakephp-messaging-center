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
namespace MessagingCenter\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\Utility\Hash;
use MessagingCenter\Model\Table\MessagesTable;

/**
 * Message Entity.
 *
 * @property string $id
 * @property string $from_user
 * @property string $to_user
 * @property string $subject
 * @property string $content
 * @property \Cake\I18n\Time $date_sent
 * @property string $status
 * @property string $related_model
 * @property string $related_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 */
class Message extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Virtual fields for sender and recipients
     * @var array
     */
    protected $_virtual = ['sender', 'sender_address', 'recipients', 'recipient_addresses'];

    /**
     * Returns the user display name for the specified field.
     * @param string $field field prefix value
     * @return string
     */
    protected function getUser(string $field) : string
    {
        $user = $this->has($field . 'User') ? $this->get($field . 'User') : $this->get($field . '_user');
        $systemUser = Configure::readOrFail('MessagingCenter.systemUser');

        if ($user instanceof Entity) {
            $firstName = $user->get('first_name') ?? '';
            $lastName = $user->get('last_name') ?? '';
            $userName = $user->get('username') ?? '';

            $fullName = trim($firstName . ' ' . $lastName);
            $displayUser = $fullName ?: $userName;

            return (string)$displayUser;
        }

        if (is_string($user) && $systemUser['id'] === $user) {
            return (string)$systemUser['name'];
        }

        if ($this->has('headers')) {
            $headers = $this->get('headers');
            if (!empty($headers[$field . 'address'])) {
                return (string)$headers[$field . 'address'];
            }
        }

        return (string)$user;
    }

    /**
     * Returns the email addresses found for the specified field
     *
     * @param string $field field prefix value
     * @return string[]
     */
    protected function getEmailAddresses(string $field) : array
    {
        $user = $this->has($field . 'User') ? $this->get($field . 'User') : $this->get($field . '_user');
        $systemUser = Configure::readOrFail('MessagingCenter.systemUser');

        if ($user instanceof Entity) {
            return [$user->get('email')];
        }

        if (is_string($user) && $systemUser['id'] === $user) {
            return [];
        }

        if ($this->has('headers')) {
            $headers = $this->get('headers');
            if (!empty($headers[$field . 'address'])) {
                return (array)Hash::format(
                    $headers[$field],
                    ['{n}.mailbox', '{n}.host'],
                    '%1$s@%2$s'
                );
            }
        }

        return [];
    }

    /**
     * Returns the display name for the sender
     *
     * @return string
     */
    protected function _getSender(): string
    {
        return $this->getUser('from');
    }

    /**
     * Display name for all the recipients
     *
     * @return string
     */
    protected function _getRecipients(): string
    {
        return $this->getUser('to');
    }

    /**
     * Returns sender's email address
     *
     * @return string
     */
    protected function _getSenderAddress(): string
    {
        $addresses = $this->getEmailAddresses('from');

        return empty($addresses[0]) ? '' : (string)$addresses[0];
    }

    /**
     * Returns the email addresses for all recipients
     *
     * @return string[]
     */
    protected function _getRecipientAddresses(): array
    {
        $emailAddressesTo = $this->getEmailAddresses('to');
        $emailAddressesCc = $this->getEmailAddresses('cc');

        return array_merge($emailAddressesTo, $emailAddressesCc);
    }

    /**
     * Moves this message to the specified folder
     *
     * @param \MessagingCenter\Model\Entity\Folder $folder Folder to move the message under
     */
    public function moveToFolder(Folder $folder): void
    {
        $this->set('folder_id', $folder->get('id'));
    }

    /**
     * Marks the message as read
     */
    public function markAsRead(): void
    {
        $this->set('status', MessagesTable::STATUS_READ);
    }
}
