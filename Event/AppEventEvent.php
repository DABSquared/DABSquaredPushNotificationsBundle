<?php

namespace DABSquared\PushNotificationsBundle\Event;

use DABSquared\PushNotificationsBundle\Model\AppEventInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:26 PM
 * To change this template use File | Settings | File Templates.
 */
class AppEventEvent extends Event
{

    private $appEvent;

    /**
     * Constructs an event.
     *
     * @param \DABSquared\PushNotificationsBundle\Model\AppEventInterface $appEvent
     */
    public function __construct(AppEventInterface $appEvent)
    {
        $this->device = $appEvent;
    }

    /**
     * Returns the app event for this event.
     *
     * @return \DABSquared\PushNotificationsBundle\Model\AppEventInterface
     */
    public function getAppEvent()
    {
        return $this->appEvent;
    }

}
