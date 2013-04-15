<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Model\MessageInterface;

interface OSNotificationServiceInterface
{
    /**
     * Send a notification message
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface $message
     * @return mixed
     */
    public function send(MessageInterface $message);

    /**
     * Sends a set of messages
     *
     * @param array $message
     * @throws \RuntimeException
     * @return bool
     */
    public function sendMessages(array $messages);
}
