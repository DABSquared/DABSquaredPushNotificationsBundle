<?php

namespace DABSquared\PushNotificationsBundle\Event;

use DABSquared\PushNotificationsBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:26 PM
 * To change this template use File | Settings | File Templates.
 */
class MessageEvent extends Event
{

    private $message;

    /**
     * Constructs an event.
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

    /**
     * Returns the message for this event.
     *
     * @return \DABSquared\PushNotificationsBundle\Model\MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

}
