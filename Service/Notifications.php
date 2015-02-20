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
     * The apps configured
     *
     * @var array
     */
    protected $apps;

    /**
     * Constructor
     */
    public function __construct($apps)
    {
        $this->apps = $apps;
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
     * @param array $messages
     * @return bool
     * @throws \RuntimeException
     */
    public function sendMessages(array $messages)
    {
        $messageTypes = array();

        /** @var \DABSquared\PushNotificationsBundle\Model\MessageInterface $message */
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
     * Returns the apps from the configuration
     *
     * @return array
     */
    public function getApps()
    {
        return $this->apps;
    }

    /**
     * Returns the app name from the configuration
     *
     * @param $internalId
     * @return null|string
     */
    public function getAppNameForInternalId($internalId)
    {
        foreach($this->apps as $app) {
            if($app['internal_app_id'] == $internalId) {
                return $app['name'];
            }
        }
        return null;
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
