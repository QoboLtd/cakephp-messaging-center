<?php
namespace MessagingCenter\Model\Entity;

use Cake\ORM\Entity;

/**
 * Message Entity.
 *
 * @property string $id
 * @property string $from
 * @property string $to
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
}
