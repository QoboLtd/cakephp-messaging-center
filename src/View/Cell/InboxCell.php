<?php
namespace MessagingCenter\View\Cell;

use Cake\View\Cell;

class InboxCell extends Cell
{
    const UNREAD_COUNT_FORMAT = '<span class="badge">{{text}}</span>';

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

        $this->set('unread_format', $format);
        $this->set('unread_count', (int)$unread->count());
    }
}
