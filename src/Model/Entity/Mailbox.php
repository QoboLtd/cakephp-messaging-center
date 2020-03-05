<?php
namespace MessagingCenter\Model\Entity;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use MessagingCenter\Enum\IncomingTransportType;
use Webmozart\Assert\Assert;

/**
 * Mailbox Entity
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $type
 * @property string $incoming_transport
 * @property string $incoming_settings
 * @property string $outgoing_transport
 * @property string $outgoing_settings
 * @property bool $active
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \MessagingCenter\Model\Entity\User $user
 */
class Mailbox extends Entity
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
        'user_id' => true,
        'name' => true,
        'type' => true,
        'incoming_transport' => true,
        'incoming_settings' => true,
        'outgoing_transport' => true,
        'outgoing_settings' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    protected $_virtual = ['imap_connection'];

    protected $_hidden = ['imap_connection'];

    /**
     * Builds and returns the IMAP4 Connection String
     *
     * Example: {localhost:993/imap/notls}INBOX
     *
     * @return string
     */
    protected function _getImapConnection(): string
    {
        if ($this->get('incoming_transport') !== (string)IncomingTransportType::IMAP4()) {
            return '';
        }

        $connectionString = '';

        $defaultSettings = Configure::readOrFail('MessagingCenter.Mailbox.default.incoming_settings');
        Assert::keyExists($defaultSettings, 'host');
        Assert::keyExists($defaultSettings, 'port');
        Assert::keyExists($defaultSettings, 'protocol');

        $settings = array_merge($defaultSettings, $this->get('incoming_settings'));

        // See more details at http://php.net/manual/en/function.imap-open.php
        $connectionString .= '{';
        $connectionString .= $settings['host'];
        $connectionString .= ':' . $settings['port'];
        $connectionString .= '/' . $settings['protocol'];
        // TODO: Make this optional
        $connectionString .= '/ssl/novalidate-cert';
        $connectionString .= '}';
        // TODO: Make this flexible
        $connectionString .= 'INBOX';

        return $connectionString;
    }
}
