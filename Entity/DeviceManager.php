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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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


    public function findDeviceByTypeIdentifierAndAppId($type,$deviceIdentifier, $appId) {
        $qb = $this->repository
            ->createQueryBuilder('d')
            ->where('d.type = :type')
            ->andWhere('d.deviceIdentifier = :deviceIdentifier')
            ->andWhere('d.appId = :appId')
            ->setParameter('type', $type)
            ->setParameter('deviceIdentifier', $deviceIdentifier)
            ->setParameter('appId', $appId);

        $devices = $qb
            ->getQuery()
            ->execute();

        if(is_null($devices)) {
            return null;
        }

        if(count($devices) != 1) {
            return null;
        }



        return $devices[0];

    }


    public function findDeviceWithName($searchTerm) {
        $qb = $this->repository
            ->createQueryBuilder('d')
            ->where("d.deviceName LIKE '%$searchTerm%'");

        $devices = $qb
            ->getQuery()
            ->execute();
        return $devices;
    }

    public function findDeviceWithId($id) {
        $qb = $this->repository
            ->createQueryBuilder('d')
            ->where('d.id = :id')
            ->setParameter('id', $id);

        $devices = $qb
            ->getQuery()
            ->execute();


        if(is_null($devices)) {
            return null;
        }

        if(count($devices) != 1) {
            return null;
        }



        return $devices[0];
    }

    public function findDevicesWithTypeAndStatus($type, $status) {
        $qb = $this->repository
            ->createQueryBuilder('d')
            ->where('d.type = :type')
            ->andWhere('d.status = :status')
            ->setParameter('type', $type)
            ->setParameter('status', $status);

        $devices = $qb
            ->getQuery()
            ->execute();
        return $devices;
    }

}
