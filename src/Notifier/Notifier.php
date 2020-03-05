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
namespace MessagingCenter\Notifier;

use Cake\View\ViewVarsTrait;
use InvalidArgumentException;

/**
 * @property \Cake\View\ViewBuilder $viewBuilder
 */
class Notifier implements NotifierInterface
{
    use ViewVarsTrait;

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
     * @var string|array
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
        'message',
    ];

    /**
     * Message template setter.
     *
     * @throws \InvalidArgumentException when the template is empty
     * @param  string $template Template name
     * @return void
     */
    public function template(string $template): void
    {
        if (empty($template)) {
            throw new InvalidArgumentException('Template cannot be empty.');
        }

        $this->viewBuilder()->setTemplate($template);
    }

    /**
     * Notification from user setter.
     *
     * @param  string $from From user
     * @return void
     */
    public function from(string $from): void
    {
        $this->_from = $from;
    }

    /**
     * Notification to user setter.
     *
     * @param  string $to To user
     * @return void
     */
    public function to(string $to): void
    {
        $this->_to = $to;
    }

    /**
     * Notification subject setter.
     *
     * @param  string $subject Subject
     * @return void
     */
    public function subject(string $subject): void
    {
        $this->_subject = $subject;
    }

    /**
     * Notification message setter.
     *
     * @param  string|array $message Message
     * @return void
     */
    public function message($message): void
    {
        if (!$this->viewBuilder()->getTemplate()) {
            $this->template('');
        }

        $View = $this->createView();

        list($plugin) = pluginSplit($View->getTemplate());
        if ($plugin) {
            $View->plugin = $plugin;
        }

        $View->hasRendered = false;
        $View->setTemplatePath('Notifier');
        $View->setLayoutPath('Notifier');

        if (is_array($message)) {
            foreach ($message as $k => $v) {
                $View->set($k, $v);
            }
        } else {
            $View->set('content', $message);
        }

        /**
         * @var string $message
         */
        $message = $View->render();
        $this->_message = $message;
    }

    /**
     * Sends notification.
     *
     * @return void
     */
    public function send(): void
    {
        // validate notification data
        $this->validate();

        // send message
    }

    /**
     * Validate notification data.
     *
     * @throws \InvalidArgumentException when validation fails
     * @return void
     */
    public function validate(): void
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
