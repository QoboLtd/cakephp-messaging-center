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
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\View\Cell;
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
        $user = $this->request->getSession()->read('Auth.User');
        if (trim($format) === '') {
            $format = static::UNREAD_COUNT_FORMAT;
        }

        if (empty($mailbox)) {
            $mailboxes = TableRegistry::getTableLocator()->get('MessagingCenter.Mailboxes');
            Assert::isInstanceOf($mailboxes, Table::class);

            $mailbox = $mailboxes->getSystemMailbox($user);
            Assert::isInstanceOf($mailbox, EntityInterface::class);
        }

        $mailboxId = $mailbox->get('id');
        $this->loadModel('MessagingCenter.Messages');
        $unread = $this->Messages->find('all')
            ->where([
                'status' => $this->Messages->getNewStatus(),
            ])
            ->contain([
                'Folders' => function ($q) use ($mailboxId) {
                    return $q->where(['mailbox_id' => $mailboxId]);
                }
            ]);

        $this->set('unreadFormat', $format);
        $this->set('unreadCount', $unread->count());
        $this->set('maxUnreadCount', static::MAX_UNREAD_COUNT);
    }

    /**
     * Pass unread emails to the Cell View
     *
     * @param  int $limit query limit
     * @param  int $contentLength content excerpt length
     * @return void
     */
    public function unreadMessages(int $limit = 10, int $contentLength = 100): void
    {
        $userId = $this->request->getSession()->read('Auth.User.id');
        $this->loadModel('MessagingCenter.Messages');
        $messages = $this->Messages->find('all', [
            'conditions' => [
                'to_user' => $userId,
                'status' => $this->Messages->getNewStatus()
            ],
            'contain' => [],
            'order' => ['Messages.date_sent' => 'DESC'],
            'limit' => $limit
        ]);

        $this->set(compact('messages', 'contentLength'));
    }
}
