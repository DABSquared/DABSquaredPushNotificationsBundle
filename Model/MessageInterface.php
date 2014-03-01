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
     * @return \DateTime
     */
    public function getCreatedAt();


    public function setSound($sound);

    public function setContentAvailable($contentAvailable);

    public function setMessage($message);

    public function setStatus($status);

    public function setData($data);

    public function setDevice(\DABSquared\PushNotificationsBundle\Model\DeviceInterface $device);

    public function getMessageBody();

    /** @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface */
    public function getDevice();

    public function getTargetOS();

    public function setTargetOS($type);

}
