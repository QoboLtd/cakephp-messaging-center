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
namespace MessagingCenter\Event\Model;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Mailer\Email;
use Cake\Utility\Inflector;
use MessagingCenter\Event\EventName;
use Webmozart\Assert\Assert;

class SendEmailListener implements EventListenerInterface
{
    /**
     * implementedEvents method
     *
     * @return mixed[]
     */
    public function implementedEvents(): array
    {
        return [
            (string)EventName::SEND_EMAIL() => 'sendEmail',
        ];
    }

    /**
     * sendEmail method
     *
     * @param \Cake\Event\Event $event event.
     * @param \Cake\Datasource\EntityInterface $mailbox to get config from
     * @param mixed[] $data to build email
     * @return void
     */
    public function sendEmail(Event $event, EntityInterface $mailbox, array $data): void
    {
        if ($mailbox->get('type') !== 'email') {
            return;
        }

        $outgoingSettings = $mailbox->get('outgoing_settings');

        Email::configTransport('custom', [
            'host' => (!empty($outgoingSettings['use_ssl']) ? 'ssl://' : '') . $outgoingSettings['host'],
            'port' => $outgoingSettings['port'],
            'username' => $outgoingSettings['username'],
            'password' => $outgoingSettings['password'],
            'className' => Inflector::camelize($mailbox->get('outgoing_transport')),
        ]);

        /**
         * @var \Cake\Mailer\Email $email
         */
        $email = new Email('default');
        Assert::isInstanceOf($email, Email::class);

        $email->setTransport('custom');
        $email->from($outgoingSettings['username']);
        $email->to($data['to_user']);
        $email->subject($data['subject']);

        $result = $email->send($data['content']);
    }
}
