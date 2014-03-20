<?php

namespace DABSquared\PushNotificationsBundle;

/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 2/10/13
 * Time: 2:09 PM
 * To change this template use File | Settings | File Templates.
 */
final class Events
{

    /**
     * The PRE_PERSIST event occurs prior to the persistence backend
     * persisting the Device.
     *
     * This event allows you to modify the data in the Device prior
     * to persisting occuring. The listener receives a
     * DABSquared\PushNotificationsBundle\Event\DevicePersistEvent instance.
     *
     * Persisting of the device can be aborted by calling
     * $event->abortPersist()
     *
     * @var string
     */
    const DEVICE_PRE_PERSIST = 'dab_push_notifications.device.pre_persist';

    /**
     * The POST_PERSIST event occurs after the persistence backend
     * persisted the Device.
     *
     * This event allows you to notify users or perform other actions
     * that might require the Device to be persisted before performing
     * those actions. The listener receives a
     * DABSquared\PushNotificationsBundle\Event\DeviceEvent instance.
     *
     * @var string
     */
    const DEVICE_POST_PERSIST = 'dab_push_notifications.device.post_persist';

    /**
     * The CREATE event occurs when the manager is asked to create
     * a new instance of a Device.
     *
     * The listener receives a DABSquared\PushNotificationsBundle\Event\DeviceEvent
     * instance.
     *
     * @var string
     */
    const DEVICE_CREATE = 'dab_push_notifications.device.create';

    /**
     * The PRE_PERSIST event occurs prior to the persistence backend
     * persisting the Message.
     *
     * This event allows you to modify the data in the Message prior
     * to persisting occuring. The listener receives a
     * DABSquared\PushNotificationsBundle\Event\MessagePersistEvent instance.
     *
     * Persisting of the message can be aborted by calling
     * $event->abortPersist()
     *
     * @var string
     */
    const MESSAGE_PRE_PERSIST = 'dab_push_notifications.message.pre_persist';

    /**
     * The POST_PERSIST event occurs after the persistence backend
     * persisted the Message.
     *
     * This event allows you to notify users or perform other actions
     * that might require the Message to be persisted before performing
     * those actions. The listener receives a
     * DABSquared\PushNotificationsBundle\Event\MessageEvent instance.
     *
     * @var string
     */
    const MESSAGE_POST_PERSIST = 'dab_push_notifications.message.post_persist';

    /**
     * The CREATE event occurs when the manager is asked to create
     * a new instance of a Message.
     *
     * The listener receives a DABSquared\PushNotificationsBundle\Event\MessageEvent
     * instance.
     *
     * @var string
     */
    const MESSAGE_CREATE = 'dab_push_notifications.message.create';

    /**
     * The PRE_PERSIST event occurs prior to the persistence backend
     * persisting the AppEvent.
     *
     * This event allows you to modify the data in the AppEvent prior
     * to persisting occuring. The listener receives a
     * DABSquared\PushNotificationsBundle\Event\AppEventPersistEvent instance.
     *
     * Persisting of the device can be aborted by calling
     * $event->abortPersist()
     *
     * @var string
     */
    const APP_EVENT_PRE_PERSIST = 'dab_push_notifications.appevent.pre_persist';

    /**
     * The POST_PERSIST event occurs after the persistence backend
     * persisted the AppEvent.
     *
     * This event allows you to notify users or perform other actions
     * that might require the AppEvent to be persisted before performing
     * those actions. The listener receives a
     * DABSquared\PushNotificationsBundle\Event\AppEventEvent instance.
     *
     * @var string
     */
    const APP_EVENT_POST_PERSIST = 'dab_push_notifications.appevent.post_persist';

    /**
     * The CREATE event occurs when the manager is asked to create
     * a new instance of a AppEvent.
     *
     * The listener receives a DABSquared\PushNotificationsBundle\Event\AppEventEvent
     * instance.
     *
     * @var string
     */
    const APP_EVENT_CREATE = 'dab_push_notifications.appevent.create';
}
