<?php
namespace MessagingCenter\Model\Entity;

use Cake\ORM\Entity;

/**
 * Folder Entity
 *
 * @property string $id
 * @property string $mailbox_id
 * @property string $parent_id
 * @property string $name
 * @property string $type
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \MessagingCenter\Model\Entity\Mailbox $mailbox
 * @property \MessagingCenter\Model\Entity\ParentFolder $parent_qobo_folder
 * @property \MessagingCenter\Model\Entity\ChildFolder[] $child_qobo_folders
 */
class Folder extends Entity
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
        'mailbox_id' => true,
        'parent_id' => true,
        'name' => true,
        'type' => true,
        'created' => true,
        'modified' => true,
        'mailbox' => true,
        'parent_qobo_folder' => true,
        'child_qobo_folders' => true
    ];
}
