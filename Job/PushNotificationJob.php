<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 2/23/15
 * Time: 2:19 PM
 */

namespace DABSquared\PushNotificationsBundle\Job;

use BCC\ResqueBundle\ContainerAwareJob;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PushNotificationJob extends ContainerAwareJob
{
    public function __construct()
    {
        $this->queue = 'dab-push-notifications';
    }

    public function run($args)
    {
        $notificationId = $args["notification_id"];

        if (is_null($notificationId)) {
            throw new \Exception("You need to provide notification id to send a push message.");
        }

        /** @var $messageManager \DABSquared\PushNotificationsBundle\Model\MessageManagerInterface */
        $messageManager = $this->getContainer()->get('dab_push_notifications.manager.message');

        /** @var $notificationManager \DABSquared\PushNotificationsBundle\Service\Notifications */
        $notificationManager = $this->getContainer()->get('dab_push_notifications');

        $messages = $messageManager->findById($notificationId);
        if(count($messages) == 0) {
            return;
        }

        $notificationManager->sendMessages($messages);
    }
}
