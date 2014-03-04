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


class iOSFeedbackCommand  extends ContainerAwareCommand{
    protected function configure() {
        $this->setName('dab:push:ios:feedback');
        $this->setDescription('Contacts iOS feedback service to correctly set device statuses.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var $iosFeedback \DABSquared\PushNotificationsBundle\Service\iOSFeedback */
        $iosFeedback = $this->getContainer()->get('dab_push_notifications.ios.feedback');

        $iosFeedback->getDeviceUUIDs();


        $messageId = $input->getArgument('messageId');





    }
}



