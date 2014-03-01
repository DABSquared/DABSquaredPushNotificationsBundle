<?php

namespace DABSquared\PushNotificationsBundle\Model;


/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:23 PM
 * To change this template use File | Settings | File Templates.
 */
interface DeviceManagerInterface
{


    /**
     * Saves a device to the persistence backend used. Each backend
     * must implement the abstract doSaveComment method which will
     * perform the saving of the comment to the backend.
     *
     * @param  DeviceInterface         $device
     * @throws InvalidArgumentException when the comment does not have a thread.
     */
    public function saveDevice(DeviceInterface $device);

    /**
     * Checks if the device was already persisted before, or if it's a new one.
     *
     * @param DeviceInterface $comment
     *
     * @return boolean True, if it's a new comment
     */
    public function isNewDevice(DeviceInterface $device);

    /**
     * creates an empty device instance
     *
     * @return Device
     */
    public function createDevice();


    /**
     * Returns the device fully qualified class name.
     *
     * @return string
     */
    public function getClass();



    /*** Find Methods  *********/


    /**
     */
    public function findDeviceByTypeIdentifierAndAppIdAndDeviceToken($type,$deviceIdentifier, $appId, $deviceToken);
    public function findDeviceWithName($searchTerm);

    public function findDeviceWithId($id);

    public function findDevicesWithTypeAndStatus($type, $status);

}
