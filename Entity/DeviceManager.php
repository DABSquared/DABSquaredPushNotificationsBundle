<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 2/10/13
 * Time: 4:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Entity;

use DABSquared\PushNotificationsBundle\Model\DeviceManager as BaseDeviceManger;

use DABSquared\PushNotificationsBundle\Model\DeviceInterface;
use DABSquared\PushNotificationsBundle\Model\MessageInterface;

class DeviceManager extends BaseDeviceManger
{

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $class;

    /**
     * Constructor.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @param \Doctrine\ORM\EntityManager                                 $em
     * @param string                                                      $class
     */
    public function __construct(EventDispatcherInterface $dispatcher, EntityManager $em, $class)
    {
        parent::__construct($dispatcher);

        $this->em = $em;
        $this->repository = $em->getRepository($class);

        $metadata = $em->getClassMetadata($class);
        $this->class = $metadata->name;
    }

    /**
     * Performs persisting of the device.
     *
     * @param DeviceInterface device
     */
    protected function doSaveDevice(DeviceInterface $device)
    {
        $this->em->persist($device);
        $this->em->flush();
    }



    /**
     * {@inheritDoc}
     */
    public function isNewDevice(DeviceInterface $device)
    {
        return !$this->em->getUnitOfWork()->isInIdentityMap($device);
    }

    /**
     * Returns the fully qualified device thread class name
     *
     * @return string
     **/
    public function getClass()
    {
        return $this->class;
    }



}
