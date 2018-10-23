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
namespace Qobo\MessagingCenter\View\Cell;

use Cake\View\Cell;

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
     * @param  string $format html format styling
     * @return void
     */
    public function unreadCount($format = '')
    {
        $userId = $this->request->session()->read('Auth.User.id');
        if ('' === trim($format)) {
            $format = static::UNREAD_COUNT_FORMAT;
        }

        $this->loadModel('Qobo/MessagingCenter.Messages');
        $unread = $this->Messages->find('all', [
            'conditions' => [
                'to_user' => $userId,
                'status' => $this->Messages->getNewStatus()
            ]
        ]);

        $this->set('unreadFormat', $format);
        $this->set('unreadCount', (int)$unread->count());
        $this->set('maxUnreadCount', static::MAX_UNREAD_COUNT);
    }

    /**
     * Pass unread emails to the Cell View
     *
     * @param  int $limit query limit
     * @param  int $contentLength content excerpt length
     * @return void
     */
    public function unreadMessages($limit = 10, $contentLength = 100)
    {
        $userId = $this->request->session()->read('Auth.User.id');
        $this->loadModel('Qobo/MessagingCenter.Messages');
        $messages = $this->Messages->find('all', [
            'conditions' => [
                'to_user' => $userId,
                'status' => $this->Messages->getNewStatus()
            ],
            'contain' => ['FromUser'],
            'order' => ['Messages.date_sent' => 'DESC'],
            'limit' => (int)$limit
        ]);

        $this->set(compact('messages', 'contentLength'));
    }
}
