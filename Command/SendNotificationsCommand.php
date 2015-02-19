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


class SendNotificationsCommand  extends ContainerAwareCommand{
    protected function configure() {
        $this->setName('dab:push:send')
            ->setDescription('Send all unsent push notifications.')
            ->setDefinition(array(
                new InputArgument('messageId',
                    InputArgument::OPTIONAL,
                    'The message to send'),));
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var $messageManager \DABSquared\PushNotificationsBundle\Model\MessageManagerInterface */
        $messageManager = $this->getContainer()->get('dab_push_notifications.manager.message');

        /** @var $notificationManager \DABSquared\PushNotificationsBundle\Service\Notifications */
        $notificationManager = $this->getContainer()->get('dab_push_notifications');


        $messageId = $input->getArgument('messageId');

        $messages = array();

        if(!is_null($messageId)) {
            $messages = $messageManager->findById($messageId);
            /** @var $message \DABSquared\PushNotificationsBundle\Model\MessageInterface */
            foreach($messages as $message) {
                if($message->getStatus() == MessageStatus::MESSAGE_STATUS_SENT) {
                    throw new InvalidParameterException("This message has already been sent");
                }
            }
        } else {
          $messages  = $messageManager->findByStatus(MessageStatus::MESSAGE_STATUS_NOT_SENT);
        }



        /** @var $message \DABSquared\PushNotificationsBundle\Model\MessageInterface */
        foreach($messages as $message) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_SENDING);
            $messageManager->saveMessage($message);
        }

        $notificationManager->sendMessages($messages);

        if(!is_null($messageId)) {
            die();
        }



        /***** Lets try and send old messages now. ********/

        $messages = $messageManager->findByStatus(MessageStatus::MESSAGE_STATUS_NO_CERT);

        /** @var $message \DABSquared\PushNotificationsBundle\Model\MessageInterface */
        foreach($messages as $message) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_SENDING);
            $messageManager->saveMessage($message);
        }

        $notificationManager->sendMessages($messages);


        $messages = $messageManager->findByStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);

        /** @var $message \DABSquared\PushNotificationsBundle\Model\MessageInterface */
        foreach($messages as $message) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_SENDING);
            $messageManager->saveMessage($message);
        }

        $notificationManager->sendMessages($messages);


        die();
    }
}



