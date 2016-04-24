<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 4/22/13
 * Time: 6:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Command;

use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use DABSquared\PushNotificationsBundle\Device\Types;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;


class iOSFeedbackCommand extends ContainerAwareCommand
{
    
    protected function configure()
    {
        $this->setName('dab:push:ios:feedback');
        $this->setDescription('Contacts iOS feedback service to correctly set device statuses.');
        $arguments = array();
        $arguments[] = new InputArgument('appId', InputArgument::OPTIONAL, 'The internal app id to get feedback for.');
        $arguments[] = new InputArgument('sandbox', InputArgument::OPTIONAL, 'Whether or not to get sandbox feedback or not.');
        $this->setDefinition($arguments);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $iosFeedback \DABSquared\PushNotificationsBundle\Service\iOSFeedback */
        $iosFeedback = $this->getContainer()->get('dab_push_notifications.ios.feedback');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManager */
        $deviceManager = $this->getContainer()->get('dab_push_notifications.manager.device');

        $appId = $input->getArgument("appId");
        $sandbox = $input->getArgument("sandbox");

        if (!is_null($sandbox)) {
            if (!filter_var($sandbox, FILTER_VALIDATE_BOOLEAN)) {
                throw new InvalidParameterException("Sandbox needs to be a 1 (True) or a 0 (False) or null for both.");
            }
        }

        $feedbacks = $iosFeedback->getDeviceFeedback($appId, $sandbox);

        /** @var $feedback \DABSquared\PushNotificationsBundle\Device\iOS\Feedback  */
        foreach ($feedbacks as $feedback) {
            $output->writeln("UUID: ".$feedback->uuid." Token Length: ". $feedback->tokenLength." Timestamp: ".$feedback->timestamp);
            $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_IOS, $feedback->uuid, $feedback->internalAppId);
            if (!is_null($device)) {
                $device->setStatus(DeviceStatus::DEVICE_STATUS_UNACTIVE);
                $deviceManager->saveDevice($device);
            }
        }
    }

}