<?php

namespace DABSquared\PushNotificationsBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;

use DABSquared\PushNotificationsBundle\Events;
use DABSquared\PushNotificationsBundle\Event\MessageEvent;
use DABSquared\PushNotificationsBundle\Event\MessagePersistEvent;

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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Constructor
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Creates an empty device instance
     *
     * @return Device
     */
    public function createMessage(MessageInterface $message)
    {
        $class = $this->getClass();
        $message = new $class;


        $event = new MessageEvent($message);
        $this->dispatcher->dispatch(Events::MESSAGE_CREATE, $event);

        return $message;
    }

    /**
     * Saves a message to the persistence backend used. Each backend
     * must implement the abstract doSaveMessage method which will
     * perform the saving of the comment to the backend.
     *
     * @param  MessageInterface         $message
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
    }

    /**
     * Performs the persistence of a message.
     *
     * @abstract
     * @param MessageInterface $message
     */
    abstract protected function doSaveMessage(MessageInterface $message);

}
