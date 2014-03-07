<?php

namespace DABSquared\PushNotificationsBundle\Model;


/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:23 PM
 * To change this template use File | Settings | File Templates.
 */
interface MessageManagerInterface
{


    /**
     * Saves a message to the persistence backend used. Each backend
     * must implement the abstract doSaveComment method which will
     * perform the saving of the message to the backend.
     *
     * @param  MessageInterface         $message
     */
    public function saveMessage(MessageInterface $message);

    /**
     * Checks if the message was already persisted before, or if it's a new one.
     *
     * @param MessageInterface $message
     *
     * @return boolean True, if it's a new message
     */
    public function isNewMessage(MessageInterface $message);

    /**
     * creates an empty device instance
     *
     * @return Message
     */
    public function createMessage();


    /**
     * Returns the device fully qualified class name.
     *
     * @return string
     */
    public function getClass();


    /**
     * @param $status
     * @return \DABSquared\PushNotificationsBundle\Model\MessageInterface
     */
    public function findByStatus($status);

    /**
     * @param $id
     * @return \DABSquared\PushNotificationsBundle\Model\MessageInterface
     */
    public function findById($id);

}
