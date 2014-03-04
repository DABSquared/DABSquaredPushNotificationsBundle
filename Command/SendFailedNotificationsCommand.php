<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 4/22/13
 * Time: 6:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Command;

use DABSquared\PushNotificationsBundle\Message\MessageStatus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;


class SendFailedNotificationsCommand  extends ContainerAwareCommand{
    protected function configure() {
        $this->setName('dab:push:send:failed');
        $this->setDescription('Send all unsent push notifications.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var $messageManager \DABSquared\PushNotificationsBundle\Model\MessageManager */
        $messageManager = $this->getContainer()->get('dab_push_notifications.manager.message');

        /** @var $notificationManager \DABSquared\PushNotificationsBundle\Service\Notifications */
        $notificationManager = $this->getContainer()->get('dab_push_notifications');

        /***** Lets try and send old messages now. ********/

        $messages = $messageManager->findByStatus(MessageStatus::MESSAGE_STATUS_NO_CERT);

        /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
        foreach($messages as $message) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_SENDING);
            $messageManager->saveMessage($message);
        }

        $notificationManager->sendMessages($messages);


        $messages = $messageManager->findByStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);

        /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
        foreach($messages as $message) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_SENDING);
            $messageManager->saveMessage($message);
        }

        $notificationManager->sendMessages($messages);


        die();
    }
}



