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

    public function __toString();

    /**
     * @return mixed database ID for this device
     */
    public function getId();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt);

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
     * @param string $appVersion
     */
    public function setAppVersion($appVersion);

    /**
     * @return string
     */
    public function getAppVersion();

    /**
     * @param string $appName
     */
    public function setAppName($appName);

    /**
     * @return string
     */
    public function getAppName();

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

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMessages();

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $messages
     */
    public function setMessages(\Doctrine\Common\Collections\ArrayCollection $messages);

    /**
     * @param MessageInterface $message
     */
    public function addMessage(\DABSquared\PushNotificationsBundle\Model\MessageInterface $message);

    /**
     * @param MessageInterface $message
     */
    public function removeMessage(\DABSquared\PushNotificationsBundle\Model\MessageInterface $message);

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAppEvents();

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $appEvents
     */
    public function setAppEvents(\Doctrine\Common\Collections\ArrayCollection $appEvents);

    /**
     * @param AppEventInterface $appEvent
     */
    public function addAppEvent(\DABSquared\PushNotificationsBundle\Model\AppEventInterface $appEvent);

    /**
     * @param AppEventInterface $appEvent
     */
    public function removeAppEvent(\DABSquared\PushNotificationsBundle\Model\AppEventInterface $appEvent);

}
