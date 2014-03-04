<?php

namespace DABSquared\PushNotificationsBundle\Model;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 1:57 PM
 * To change this template use File | Settings | File Templates.
 */
interface DeviceInterface
{

    const STATE_SANDBOX = 0;

    const STATE_PRODUCTION = 1;

    /**
     * @return mixed database ID for this device
     */
    public function getId();

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @return integer Whether or not to use sandbox mode for this device
     */
    public function getState();

    /**
     * @param integer $state
     */
    public function setState($state);


    /**
     * @return string Whether or not to use sandbox mode for this device
     */
    public function getDeviceToken();

    /**
     * @param string $deviceToken
     */
    public function setDeviceToken($deviceToken);

    /**
     * @return boolean Whether or not badges are allowed
     */
    public function getBadgeAllowed();

    /**
     * @param boolean $badgeAllowed
     */
    public function setBadgeAllowed($badgeAllowed);

    /**
     * @return boolean Whether or not sounds are allowed
     */
    public function getSoundAllowed();

    /**
     * @param boolean $soundAllowed
     */
    public function setSoundAllowed($soundAllowed);

    /**
     * @return boolean Whether or not alerts are allowed
     */
    public function getAlertAllowed();

    /**
     * @param boolean $alertAllowed
     */
    public function setAlertAllowed($alertAllowed);


    /**
     * @return string The device name
     */
    public function getDeviceName();

    /**
     * @param string $deviceName
     */
    public function setDeviceName($deviceName);

    /**
     * @return string The device name
     */
    public function getDeviceModel();

    /**
     * @param string $deviceModel
     */
    public function setDeviceModel($deviceModel);

    /**
     * @return string The device version
     */
    public function getDeviceVersion();

    /**
     * @param string $deviceVersion
     */
    public function setDeviceVersion($deviceVersion);

    /**
     * @return string The device identifier
     */
    public function getDeviceIdentifier();

    /**
     * @param string $deviceIdentifier
     */
    public function setDeviceIdentifier($deviceIdentifier);

    /**
     * @return string The app id
     */
    public function getAppId();

    /**
     * @param string $appId
     */
    public function setAppId($appId);

    /**
     * @param string $type
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $badgeNumber
     */
    public function setBadgeNumber($badgeNumber);

    /**
     * @return int
     */
    public function getBadgeNumber();

    /**
     * @param int $status
     */
    public function setStatus($status);

    public function getMessages();

    public function setMessages($messages);

    public function addMessage(\DABSquared\PushNotificationsBundle\Model\MessageInterface $message);

    public function removeMessage(\DABSquared\PushNotificationsBundle\Model\MessageInterface $message);

}
