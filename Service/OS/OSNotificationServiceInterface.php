<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Message\MessageInterface;

interface OSNotificationServiceInterface
{
    /**
     * Send a notification message
     *
     * @param \DABSquared\PushNotificationsBundle\Message\MessageInterface $message
     * @return mixed
     */
    public function send(MessageInterface $message);
}
