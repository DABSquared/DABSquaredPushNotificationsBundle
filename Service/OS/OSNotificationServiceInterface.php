<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Model\MessageInterface;

interface OSNotificationServiceInterface
{
    /**
     * Send a notification message
     *
     * @param MessageInterface $message
     * @return bool|void
     * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     */
    public function send(MessageInterface $message);

    /**
     * Sends a set of messages
     *
     * @param array $messages
     * @return bool
     * @throws \RuntimeException
     */
    public function sendMessages(array $messages);

}
