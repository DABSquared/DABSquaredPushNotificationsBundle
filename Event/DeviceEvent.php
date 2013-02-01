<?php

namespace DABSquared\PushNotificationsBundle\Event;

use DABSquared\PushNotificationsBundle\Model\DeviceInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DeviceEvent extends Event
{

    private $device;

    /**
     * Constructs an event.
     *
     * @param \DABSquared\PushNotificationsBundle\Model\DeviceInterface $device
     */
    public function __construct(DeviceInterface $device)
    {
        $this->device = $device;
    }

    /**
     * Returns the comment for this event.
     *
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function getDevice()
    {
        return $this->device;
    }

}
