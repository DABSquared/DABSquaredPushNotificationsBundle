<?php

namespace DABSquared\PushNotificationsBundle\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use InvalidArgumentException;

use DABSquared\PushNotificationsBundle\Events;
use DABSquared\PushNotificationsBundle\Event\AppEventEvent;
use DABSquared\PushNotificationsBundle\Event\AppEventPersistEvent;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:20 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class AppEventManager implements AppEventManagerInterface
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
     * @return AppEvent
     */
    public function createAppEvent()
    {
        $class = $this->getClass();
        $appEvent = new $class;


        $event = new AppEventEvent($appEvent);
        $this->dispatcher->dispatch(Events::APP_EVENT_CREATE, $event);

        return $appEvent;
    }

    /**
     * Saves a device to the persistence backend used. Each backend
     * must implement the abstract doSaveComment method which will
     * perform the saving of the comment to the backend.
     *
     * @param  AppEventInterface         $appEvent
     * @throws InvalidArgumentException when the comment does not have a thread.
     */
    public function saveAppEvent(AppEventInterface $appEvent)
    {
        $event = new AppEventPersistEvent($appEvent);
        $this->dispatcher->dispatch(Events::APP_EVENT_PRE_PERSIST, $event);

        if ($event->isPersistenceAborted()) {
            return;
        }

        $this->doSaveAppEvent($appEvent);

        $event = new AppEventEvent($appEvent);
        $this->dispatcher->dispatch(Events::APP_EVENT_POST_PERSIST, $event);
    }

    /**
     * Performs the persistence of an app event.
     *
     * @abstract
     * @param AppEventInterface $appEvent
     */
    abstract protected function doSaveAppEvent(AppEventInterface $appEvent);

}
