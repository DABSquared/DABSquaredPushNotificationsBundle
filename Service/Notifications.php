<?php

namespace DABSquared\PushNotificationsBundle\Service;

use DABSquared\PushNotificationsBundle\Model\MessageInterface;

class Notifications
{
    /**
     * Array of handlers
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Sends a message to a device, identified by
     * the OS and the supplied device token
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface $message
     * @throws \RuntimeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!isset($this->handlers[$message->getTargetOS()])) {
            throw new \RuntimeException("OS type {$message->getTargetOS()} not supported");
        }

        return $this->handlers[$message->getTargetOS()]->send($message);
    }


    /**
     * Sends a set of messages
     *
     * @param array $message
     * @throws \RuntimeException
     * @return bool
     */
    public function sendMessages(array $messages)
    {
        $messageTypes = array();

        foreach($messages as $message) {
            if (!isset($this->handlers[$message->getTargetOS()])) {
                throw new \RuntimeException("OS type {$message->getTargetOS()} not supported");
            }

            if(!array_key_exists($message->getTargetOS(), $messageTypes)) {
                $messageTypes[$message->getTargetOS()] = array();
            }

            $messageTypes[$message->getTargetOS()][] = $message;
        }

        $return = false;


        foreach($messageTypes as $key => $messageType) {
            if($return) {
                return $return;
            }


            $return = $this->handlers[$key]->sendMessages($messageType);
        }

        return $return;
    }

    /**
     * Adds a handler
     *
     * @param $osType
     * @param $service
     */
    public function addHandler($osType, $service)
    {
        if (!isset($this->handlers[$osType])) {
            $this->handlers[$osType] = $service;
        }
    }
}
