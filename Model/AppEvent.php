<?php

namespace DABSquared\PushNotificationsBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 1:57 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class AppEvent implements AppEventInterface
{

    /**
     * Device database id
     *
     * @var mixed
     */
    protected $id;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    protected $device;



    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return mixed database ID for this app event
     */
    public function getId() {
        return $this->id;
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

    /**
     * Set App Event Type
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Get App Event Type
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param \DABSquared\PushNotificationsBundle\Model\DeviceInterface $device
     */
    public function setDevice(\DABSquared\PushNotificationsBundle\Model\DeviceInterface $device)
    {
        $this->device = $device;
    }

    /**
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function getDevice()
    {
        return $this->device;
    }

}
