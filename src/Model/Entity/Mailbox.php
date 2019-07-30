<?php
namespace MessagingCenter\Model\Entity;

use Cake\ORM\Entity;

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
        'default_folder' => true
    ];
}
