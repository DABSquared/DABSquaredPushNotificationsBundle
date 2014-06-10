<?php

namespace DABSquared\PushNotificationsBundle\Model;

interface MessageInterface
{
    const DEFAULT_COLLAPSE_KEY = "1";

    /**
     * @return mixed database ID for this device
     */
    public function getId();

    /**
     * Sets the creation date
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param string $message
     */
    public function setMessage($message);

    /**
     * Returns the string message
     * @return string
     */
    public function getMessage();

    /**
     *
     * @param $title
     */
    public function setTitle($title);

    /**
     * Returns the string title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Gets the full message body to send to APN
     * @return array
     */
    public function getMessageBody();

    /**
     * Sets any custom data for the message
     * @param $data
     * @throws \InvalidArgumentException
     */
    public function setData($data);

    /**
     * Returns any custom data
     * @return array
     */
    public function getData();

    /**
     * @param string $key
     * @param mixed $value
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addCustomData($key, $value);

    /**
     * @param string $sound
     */
    public function setSound($sound);

    /**
     * @return string
     */
    public function getSound();

    /**
     * @param bool $contentAvailable
     */
    public function setContentAvailable($contentAvailable);

    /**
     * @return bool
     */
    public function getContentAvailable();

    /**
     * @param $type
     */
    public function setTargetOS($type);

    /**
     * Returns the target OS for this message
     * @return string
     */
    public function getTargetOS();

    /**
     * Android-specific
     * Returns the collapse key
     *
     * @return int
     */
    public function getCollapseKey();

    /**
     * Android-specific
     * Sets the collapse key
     *
     * @param $collapseKey
     */
    public function setCollapseKey($collapseKey);

    /**
     * @param boolean $isGCM
     */
    public function setIsGCM($isGCM);

    /**
     * @return boolean
     */
    public function getIsGCM();

    /**
     * Sets GCM options
     * @param array $options
     */
    public function setGCMOptions($options);

    /**
     * Returns GCM options
     * @return array
     */
    public function getGCMOptions();


    /**
     * @param array $apsBody
     */
    public function setApsBody($apsBody);

    /**
     * @return array
     */
    public function getApsBody();

    /**
     * @param array $customData
     */
    public function setCustomData($customData);

    /**
     * @return array
     */
    public function getCustomData();

    /**
     * @param \DABSquared\PushNotificationsBundle\Model\DeviceInterface $device
     */
    public function setDevice($device);

    /**
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function getDevice();


    /**
     * @param string $status
     */
    public function setStatus($status);

    /**
     * @return string
     */
    public function getStatus();

}
