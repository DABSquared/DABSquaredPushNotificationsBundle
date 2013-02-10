<?php

namespace DABSquared\PushNotificationsBundle\Model;

interface MessageInterface
{

    const DEFAULT_COLLAPSE_KEY = 1;



    /**
     * @return mixed database ID for this device
     */
    public function getId();

    /**
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @return DateTime
     */
    public function getUpdatedAt();


    /**
     * @return integer What Message type
     */
    public function getMessageType();

    /**
     * @param integer $type
     */
    public function setMessageType($type);

    /**
     * @param $number
     * @return mixed
     */
    public function setBadgeNumber($number);

    /**
     * @param $number
     * @return mixed
     */
    public function getBadgeNumber($number);

    public function setSound($sound);

    public function getSound();

    public function setMessage($message);

    public function setData($data);

    public function setDevice($device);

    public function getMessageBody();

    public function getDevice();

    public function getTargetOS();

    public function setTargetOS($type);

}
