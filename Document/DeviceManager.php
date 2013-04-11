<?php

/**
 * This file is part of the FOSCommentBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DABSquared\PushNotificationsBundle\Entity;

use DABSquared\PushNotificationsBundle\Model\DeviceManager as BaseDeviceManager;


use Doctrine\ODM\MongoDB\DocumentManager;
use DABSquared\PushNotificationsBundle\Model\DeviceInterface;
use DABSquared\PushNotificationsBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Default ODM CommentManager.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
class DeviceManager extends BaseDeviceManager
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var DocumentRepository
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
     * @param \Doctrine\ODM\MongoDB\DocumentManager                       $dm
     * @param string                                                      $class
     */
    public function __construct(EventDispatcherInterface $dispatcher, DocumentManager $dm, $class)
    {
        parent::__construct($dispatcher);

        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);

        $metadata = $dm->getClassMetadata($class);
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
        return !$this->dm->getUnitOfWork()->isInIdentityMap($device);
    }

    /**
     * Returns the fully qualified comment thread class name
     *
     * @return string
     **/
    public function getClass()
    {
        return $this->class;
    }


    public function findDeviceByTypeAndToken($type, $token) {
        $qb = $this->repository
            ->createQueryBuilder()
            ->field('type')->equals($type)
            ->field('deviceToken')->equals($token);

        $devices = $qb
            ->getQuery()
            ->execute();

        if(is_null($devices)) {
            return null;
        }

        if(count($devices) > 1) {
            return null;
        }

        return $devices[0];

    }

}
