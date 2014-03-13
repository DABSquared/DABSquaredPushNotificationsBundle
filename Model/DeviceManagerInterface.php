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
     * @return mixed
     */
    public function findAllDevicesQuery();


    /**
     * @param $type
     * @param $deviceIdentifier
     * @param $appId
     * @param $deviceToken
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function findDeviceByTypeIdentifierAndAppIdAndDeviceToken($type,$deviceIdentifier, $appId, $deviceToken);


    /**
     * @param $type
     * @param $deviceIdentifier
     * @param $appId
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function findDeviceByTypeIdentifierAndAppId($type,$deviceIdentifier, $appId);


    /**
     * @param $searchTerm
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function findDeviceWithName($searchTerm);

    /**
     * @param $id
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function findDeviceWithId($id);

    /**
     * @param $type
     * @param $status
     * @return array
     */
    public function findDevicesWithTypeAndStatus($type, $status);

}
