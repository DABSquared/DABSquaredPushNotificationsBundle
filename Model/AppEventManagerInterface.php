<?php

namespace DABSquared\PushNotificationsBundle\Model;


/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:23 PM
 * To change this template use File | Settings | File Templates.
 */
interface AppEventManagerInterface
{

    /**
     * Returns the device fully qualified class name.
     *
     * @return string
     */
    public function getClass();

    /**
     * @param $appEventTypes
     * @param $deviceTypes
     * @param $appIds
     * @param $deviceState
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return AppEventInterface[]
     */
    public function getAppEvents($appEventTypes, $deviceTypes, $appIds, $deviceState, \DateTime $startDate, \DateTime $endDate);

    /**
     * @return AppEventInterface
     */
    public function createAppEvent();

    /**
     * @param AppEventInterface $appEvent
     */
    public function saveAppEvent(AppEventInterface $appEvent);
}
