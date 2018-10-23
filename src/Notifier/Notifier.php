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
namespace Qobo\MessagingCenter\Notifier;

use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;

class Notifier implements NotifierInterface
{
    /**
     * Notification From user.
     *
     * @var string
     */
    protected $_from;

    /**
     * Notification To user.
     *
     * @var string
     */
    protected $_to;

    /**
     * Notification subject.
     *
     * @var string
     */
    protected $_subject;

    /**
     * Notification message.
     *
     * @var string
     */
    protected $_message;

    /**
     * Notification required fields
     *
     * @var array
     */
    protected $_requiredFields = [
        'from',
        'to',
        'subject',
        'message'
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function template($template)
    {
        if (!$template) {
            throw new InvalidArgumentException('Template cannot be empty.');
        }

        $this->viewBuilder()->template($template);
    }

    /**
     * {@inheritDoc}
     */
    public function from($from)
    {
        $this->_from = $from;
    }

    /**
     * {@inheritDoc}
     */
    public function to($to)
    {
        $this->_to = $to;
    }

    /**
     * {@inheritDoc}
     */
    public function subject($subject)
    {
        $this->_subject = $subject;
    }

    /**
     * {@inheritDoc}
     */
    public function message($message)
    {
        if (!$this->viewBuilder()->template()) {
            $this->template();
        }

        $View = $this->createView();

        list($plugin) = pluginSplit($View->template());
        if ($plugin) {
            $View->plugin = $plugin;
        }

        $View->hasRendered = false;
        $View->templatePath('Notifier');
        $View->layoutPath('Notifier');

        if (is_array($message)) {
            foreach ($message as $k => $v) {
                $View->set($k, $v);
            }
        } else {
            $View->set('content', $message);
        }

        $this->_message = $View->render();
    }

    /**
     * {@inheritDoc}
     */
    public function send()
    {
        // validate notification data
        $this->validate();

        // send message
    }

    /**
     * {@inheritDoc}
     */
    public function validate()
    {
        // check for empty values
        foreach ($this->_requiredFields as $field) {
            $property = '_' . $field;
            if (!empty($this->{$property})) {
                continue;
            }
            throw new InvalidArgumentException('Field [' . $field . '] cannot be empty.');
        }
    }
}
