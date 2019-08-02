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
namespace MessagingCenter\View\Cell;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\View\Cell;
use InvalidArgumentException;
use MessagingCenter\Model\Entity\Mailbox;
use MessagingCenter\Model\Table\MailboxesTable;
use Webmozart\Assert\Assert;

/**
 * @property \MessagingCenter\Model\Table\MessagesTable $Messages
 */
class InboxCell extends Cell
{
    /**
     * Unread count default format
     */
    const UNREAD_COUNT_FORMAT = '<span class="badge">{{text}}</span>';

    /**
     * Max unread count to display in the View
     */
    const MAX_UNREAD_COUNT = 100;

    /**
     * Pass unread emails count to the Cell View
     *
     * @param  string $format html format styling
     * @param \Cake\Datasource\EntityInterface $mailbox to check new messages for
     * @return void
     */
    public function unreadCount(string $format = '', ?EntityInterface $mailbox = null): void
    {
        if (trim($format) === '') {
            $format = static::UNREAD_COUNT_FORMAT;
        }

        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);

        if (empty($mailbox)) {
            try {
                $user = $this->request->getSession()->read('Auth.User');
                Assert::isArray($user);

                $mailbox = $mailboxes->getSystemMailbox($user);
            } catch (InvalidArgumentException $e) {
                $this->set('unreadFormat', $format);
                $this->set('unreadCount', 0);
                $this->set('maxUnreadCount', static::MAX_UNREAD_COUNT);

                return;
            }
        }

        Assert::isInstanceOf($mailbox, Mailbox::class);
        $unreadCount = $mailboxes->countUnreadMessages($mailbox);

        $this->set('unreadFormat', $format);
        $this->set('unreadCount', $unreadCount);
        $this->set('maxUnreadCount', static::MAX_UNREAD_COUNT);
    }

    /**
     * Pass unread emails to the Cell View
     *
     * @param  int $limit query limit
     * @param  int $contentLength content excerpt length
     * @return void
     */
    public function unreadMessages(int $limit = 10, int $contentLength = 100, ?EntityInterface $mailbox = null): void
    {
        $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
        Assert::isInstanceOf($mailboxes, MailboxesTable::class);

        if (empty($mailbox)) {
            try {
                $user = $this->request->getSession()->read('Auth.User');
                Assert::isArray($user);

                $mailbox = $mailboxes->getSystemMailbox($user);
            } catch (InvalidArgumentException $e) {
                $this->set('messages', []);
                $this->set('contentLength', $contentLength);

                return;
            }
        }

        Assert::isInstanceOf($mailbox, Mailbox::class);
        $messages = $mailboxes->getUnreadMessages($mailbox, $limit);

        $this->set(compact('messages', 'contentLength'));
    }
}
