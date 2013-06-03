<?php

namespace DABSquared\PushNotificationsBundle\Model;

use DABSquared\PushNotificationsBundle\Device\DeviceStatus;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 1:57 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class Device implements DeviceInterface
{



    /**
     * Device database id
     *
     * @var mixed
     */
    protected $id;

    /**
     * Current state of the device.
     *
     * @var integer
     */
    protected $state = Device::STATE_PRODUCTION;

    /**
     * Current Badge Number.
     *
     * @var integer
     */
    protected $badgeNumber = 0;

    /**
     * Device token
     *
     * @var string
     */
    protected $deviceToken;

    /**
     * @var boolean
     */
    protected $badgeAllowed = false;

    /**
     * @var boolean
     */
    protected $soundAllowed = false;

    /**
     * @var boolean
     */
    protected $alertAllowed = false;

    /**
     * @var string
     */
    protected $deviceName;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $status = DeviceStatus::DEVICE_STATUS_ACTIVE;

    /**
     * @var string
     */
    protected $deviceModel;

    /**
     * @var mixed
     */
    protected $messages;

    /**
     * @var string
     */
    protected $deviceVersion;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @var string
     */
    protected $deviceIdentifier;


    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $appVersion;


    protected $createdAt;


    protected $updatedAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }



    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }


    public function __toString()
    {
        return 'Device #'.$this->getId(). 'Device Token:'.$this->getDeviceToken();
    }

    /**
     * @return mixed database ID for this device
     */
    public function getId(){
       return $this->id;
    }

    /**
     * @param boolean $alertAllowed
     */
    public function setAlertAllowed($alertAllowed)
    {
        $this->alertAllowed = $alertAllowed;
    }

    /**
     * @return boolean
     */
    public function getAlertAllowed()
    {
        return $this->alertAllowed;
    }

    /**
     * @param boolean $badgeAllowed
     */
    public function setBadgeAllowed($badgeAllowed)
    {
        $this->badgeAllowed = $badgeAllowed;
    }

    /**
     * @return boolean
     */
    public function getBadgeAllowed()
    {
        return $this->badgeAllowed;
    }

    /**
     * @param string $deviceModel
     */
    public function setDeviceModel($deviceModel)
    {
        $this->deviceModel = $deviceModel;
    }

    /**
     * @return string
     */
    public function getDeviceModel()
    {
        return $this->deviceModel;
    }

    /**
     * @param string $deviceName
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;
    }

    /**
     * @return string
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * @param string $deviceToken
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;
    }

    /**
     * @return string
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }

    /**
     * @param string $deviceVersion
     */
    public function setDeviceVersion($deviceVersion)
    {
        $this->deviceVersion = $deviceVersion;
    }

    /**
     * @return string
     */
    public function getDeviceVersion()
    {
        return $this->deviceVersion;
    }

    /**
     * @param boolean $soundAllowed
     */
    public function setSoundAllowed($soundAllowed)
    {
        $this->soundAllowed = $soundAllowed;
    }

    /**
     * @return boolean
     */
    public function getSoundAllowed()
    {
        return $this->soundAllowed;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }


    public function getMessages() {
        return $this->messages;
    }

    public function setMessages($messages) {
        $this->messages = $messages;
    }

    public function addMessage($message) {
        $message->setDevice($this);
        $this->messages[] = $message;
    }

    public function removeMessage($message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * @param string $appId
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;
    }

    /**
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }
    /**
     * @param string $deviceIdentifier
     */
    public function setDeviceIdentifier($deviceIdentifier)
    {
        $this->deviceIdentifier = $deviceIdentifier;
    }

    /**
     * @return string
     */
    public function getDeviceIdentifier()
    {
        return $this->deviceIdentifier;
    }
    /**
     * @param string $appName
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param string $appVersion
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * @param int $badgeNumber
     */
    public function setBadgeNumber($badgeNumber)
    {
        $this->badgeNumber = $badgeNumber;
    }

    /**
     * @return int
     */
    public function getBadgeNumber()
    {
        return $this->badgeNumber;
    }

    /**
 * @param string $status
 */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }



}
