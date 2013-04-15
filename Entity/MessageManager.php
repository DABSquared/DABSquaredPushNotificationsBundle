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

class MessageManager extends BaseMessageManger
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
     * Performs persisting of the message.
     *
     * @param MessageInterface message
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

}
