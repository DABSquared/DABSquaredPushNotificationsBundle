<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 2/10/13
 * Time: 5:01 PM
 * To change this template use File | Settings | File Templates.
 */


namespace DABSquared\PushNotificationsBundle\Entity;

use DABSquared\PushNotificationsBundle\Model\MessageManager as BaseMessageManger;

use DABSquared\PushNotificationsBundle\Model\DeviceInterface;
use DABSquared\PushNotificationsBundle\Model\MessageInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

class MessageManager extends BaseMessageManger
{


    /**
     * @param Container $container
     * @param EventDispatcherInterface $dispatcher
     * @param EntityManager $em
     * @param $class
     * @param $useBCCResque
     * @param $bccResqueQueue
     */
    public function __construct(Container $container, EventDispatcherInterface $dispatcher, $em, $class, $useBCCResque, $bccResqueQueue)
    {
        parent::__construct($container, $dispatcher, $em, $class, $useBCCResque, $bccResqueQueue);
    }

    /**
     * Performs persisting of the message.
     *
     * @param MessageInterface $message
     */
    protected function doSaveMessage(MessageInterface $message)
    {
        $this->em->persist($message);
        $this->em->flush();
    }



    /**
     * {@inheritDoc}
     */
    public function isNewMessage(MessageInterface $message)
    {
        return !$this->em->getUnitOfWork()->isInIdentityMap($message);
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


    public function findByStatus($status) {
        $qb = $this->repository
            ->createQueryBuilder('m')
            ->where('m.status = :status')
            ->setParameter('status', $status);

        $messages = $qb
            ->getQuery()
            ->execute();

        return $messages;

    }

    public function findById($id) {
        $qb = $this->repository
            ->createQueryBuilder('m')
            ->where('m.id = :id')
            ->setParameter('id', $id);

        $messages = $qb
            ->getQuery()
            ->execute();

        return $messages;

    }

    public function findAllQueryByDeviceId($id) {
        $qb = $this->repository
            ->createQueryBuilder('m')
            ->where('m.device = :id')
            ->setParameter('id', $id);

        return $qb;
    }

    public function findAllQuery() {
        $qb = $this->repository
            ->createQueryBuilder('m')
            ->join('m.device', 'd');

        return $qb;
    }

}
