<?php

namespace DABSquared\PushNotificationsBundle\Model;

use DABSquared\PushNotificationsBundle\Message\MessageStatus;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;

use DABSquared\PushNotificationsBundle\Events;
use DABSquared\PushNotificationsBundle\Job\PushNotificationJob;
use DABSquared\PushNotificationsBundle\Event\MessageEvent;
use DABSquared\PushNotificationsBundle\Event\MessagePersistEvent;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;



/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:20 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class MessageManager implements MessageManagerInterface
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

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
     * @var $resque \BCC\ResqueBundle\Resque
     */
    protected $resque = null;

    /**
     * @var boolean
     */
    protected $useBCCResque;

    /**
     * @var string
     */
    protected $bccResqueQueue;

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
        $this->em = $em;
        $this->repository = $em->getRepository($class);

        $metadata = $em->getClassMetadata($class);
        $this->class = $metadata->name;

        $this->dispatcher = $dispatcher;

        $this->useBCCResque = $useBCCResque;

        if($useBCCResque) {
            $this->resque = $container->get('bcc_resque.resque');
            $this->bccResqueQueue = $bccResqueQueue;
        }
    }

    /**
     * Creates an empty message instance
     *
     * @return Message
     */
    public function createMessage()
    {
        $class = $this->getClass();
        $message = new $class;


        $event = new MessageEvent($message);
        $this->dispatcher->dispatch(Events::MESSAGE_CREATE, $event);

        return $message;
    }

    /**
     * /**
     * Saves a message to the persistence backend used. Each backend
     * must implement the abstract doSaveMessage method which will
     * perform the saving of the comment to the backend.
     *
     * @param MessageInterface $message
     * @throws \InvalidArgumentException
     */
    public function saveMessage(MessageInterface $message)
    {
        if (null === $message->getDevice()) {
            throw new InvalidArgumentException('The message must have a device');
        }

        if (null === $message->getTargetOS()) {
            throw new InvalidArgumentException('The message must have a target os');
        }

        $event = new MessagePersistEvent($message);
        $this->dispatcher->dispatch(Events::MESSAGE_PRE_PERSIST, $event);

        if ($event->isPersistenceAborted()) {
            return;
        }

        $this->doSaveMessage($message);

        $event = new MessageEvent($message);
        $this->dispatcher->dispatch(Events::MESSAGE_POST_PERSIST, $event);

        if($this->useBCCResque && $message->getStatus() == MessageStatus::MESSAGE_STATUS_NOT_SENT) {
            $pushNotificationJob = new PushNotificationJob();
            $pushNotificationJob->args = array("notification_id" => $message->getId());
            $pushNotificationJob->queue = $this->bccResqueQueue;
            $message->setStatus(MessageStatus::MESSAGE_STATUS_QUEUED);
            $this->em->persist($message);
            $this->em->flush();
            $this->resque->enqueue($pushNotificationJob);
        }
    }

    /**
     * Performs the persistence of a message.
     *
     * @abstract
     * @param MessageInterface $message
     */
    abstract protected function doSaveMessage(MessageInterface $message);

}
