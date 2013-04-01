<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 3/31/13
 * Time: 7:12 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;


interface UserDeviceInterface extends DeviceInterface {
    /**
     * Sets the owner of the Device
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user);

    /**
     * Gets the owner of the Device
     *
     * @return UserInterface
     */
    public function getUser();
}