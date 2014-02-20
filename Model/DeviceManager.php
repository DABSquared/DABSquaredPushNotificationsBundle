<?php

namespace DABSquared\PushNotificationsBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;

use DABSquared\PushNotificationsBundle\Events;
use DABSquared\PushNotificationsBundle\Event\DeviceEvent;
use DABSquared\PushNotificationsBundle\Event\DevicePersistEvent;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:20 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class DeviceManager implements DeviceManagerInterface
{


    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Constructor
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Creates an empty device instance
     *
     * @return Device
     */
    public function createDevice()
    {
        $class = $this->getClass();
        $device = new $class;


        $event = new DeviceEvent($device);
        $this->dispatcher->dispatch(Events::DEVICE_CREATE, $event);

        return $device;
    }

    /**
     * Saves a device to the persistence backend used. Each backend
     * must implement the abstract doSaveComment method which will
     * perform the saving of the comment to the backend.
     *
     * @param  DeviceInterface         $device
     * @throws InvalidArgumentException when the comment does not have a thread.
     */
    public function saveDevice(DeviceInterface $device)
    {
        if (null === $device->getDeviceToken()) {
            throw new InvalidArgumentException('The device must have a token');
        }

        $event = new DevicePersistEvent($device);
        $this->dispatcher->dispatch(Events::DEVICE_PRE_PERSIST, $event);

        if ($event->isPersistenceAborted()) {
            return;
        }

        $this->doSaveDevice($device);

        $event = new DeviceEvent($device);
        $this->dispatcher->dispatch(Events::DEVICE_POST_PERSIST, $event);
    }

    /**
     * Performs the persistence of a device.
     *
     * @abstract
     * @param DeviceInterface $device
     */
    abstract protected function doSaveDevice(DeviceInterface $device);

}
