<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 2/10/13
 * Time: 4:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Entity;

use DABSquared\PushNotificationsBundle\Model\AppEventManager as BaseAppEventManger;

use DABSquared\PushNotificationsBundle\Model\AppEventInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class AppEventManager extends BaseAppEventManger
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
     * @param AppEventInterface $appEvent
     */
    protected function doSaveAppEvent(AppEventInterface $appEvent)
    {
        $this->em->persist($appEvent);
        $this->em->flush();
    }



    /**
     * {@inheritDoc}
     */
    public function isNewAppEvent(AppEventInterface $appEvent)
    {
        return !$this->em->getUnitOfWork()->isInIdentityMap($appEvent);
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

    public function getAppEvents($appEventTypes, $deviceTypes, $appIds, $deviceState, \DateTime $startDate, \DateTime $endDate ) {

        if(empty($appEventTypes)) {
            throw new InvalidParameterException("The app events array cannot be empty.");
        }

        if(empty($deviceTypes)) {
            throw new InvalidParameterException("The device types array cannot be empty.");
        }

        if(empty($appIds)) {
            throw new InvalidParameterException("The app ids array cannot be empty.");
        }

        $qb = $this->repository->createQueryBuilder('a');
        $qb->join('a.device','d');

        $qb->where('d.state = :state');
        $qb->setParameter('state',$deviceState);

        $i = 0;
        foreach($appEventTypes as $appEventType) {
            if($i == 0) {
                $qb->andWhere('a.type = :type'.$i);
                $qb->setParameter('type'.$i, $appEventType);
            } else {
                $qb->orWhere('a.type = :type'.$i);
                $qb->setParameter('type'.$i, $appEventType);
            }
            $i++;
        }

        $i = 0;
        foreach($deviceTypes as $deviceType) {
            if($i == 0) {
                $qb->andWhere('d.type = :deviceType'.$i);
                $qb->setParameter('deviceType'.$i, $deviceType);
            } else {
                $qb->orWhere('d.type = :deviceType'.$i);
                $qb->setParameter('deviceType'.$i, $deviceType);
            }
            $i++;
        }

        $i = 0;
        foreach($appIds as $appId) {
            if($i == 0) {
                $qb->andWhere('d.appId = :appId'.$i);
                $qb->setParameter('appId'.$i, $appId);
            } else {
                $qb->orWhere('d.appId = :appId'.$i);
                $qb->setParameter('appId'.$i, $appId);
            }
            $i++;
        }

        $qb->andWhere('a.createdAt >= :startDate');
        $qb->setParameter('startDate', $startDate);

        $qb->andWhere('a.createdAt <= :endDate');
        $qb->setParameter('endDate', $endDate);

        $appEvents = $qb
            ->getQuery()
            ->execute();

        return $appEvents;
    }

}
