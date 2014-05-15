<?php

namespace DABSquared\PushNotificationsBundle\Model;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 1:57 PM
 * To change this template use File | Settings | File Templates.
 */
interface AppEventInterface
{

    const APP_OPEN = "app_open";
    const APP_BACKGROUNDED = "app_backgrounded";
    const APP_TERMINATED = "app_terminated";

    /**
     * @return mixed database ID for this app event
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
     * Get App Event Type
     * @return string
     */
    public function getType();

    /**
     * Set App Event Type
     * @param string $type
     */
    public function setType($type);

    /**
     * @param \DABSquared\PushNotificationsBundle\Model\Device $device
     */
    public function setDevice(\DABSquared\PushNotificationsBundle\Model\Device $device);

    /**
     * @return \DABSquared\PushNotificationsBundle\Model\Device
     */
    public function getDevice();

}
