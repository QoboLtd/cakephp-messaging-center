<?php
namespace MessagingCenter\View\Cell;

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

        $this->loadModel('MessagingCenter.Messages');
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
     * @param  bool $count flag for including unread messages counter
     * @param  int $contentLength content excerpt length
     * @return void
     */
    public function unreadMessages($limit = 10, $count = false, $contentLength = 100)
    {
        $userId = $this->request->session()->read('Auth.User.id');
        $this->loadModel('MessagingCenter.Messages');
        $messages = $this->Messages->find('all', [
            'conditions' => [
                'to_user' => $userId,
                'status' => $this->Messages->getNewStatus()
            ],
            'contain' => ['FromUser'],
            'order' => ['Messages.date_sent' => 'DESC'],
            'limit' => (int)$limit
        ]);

        $this->set(compact('messages', 'count', 'contentLength'));
    }
}
