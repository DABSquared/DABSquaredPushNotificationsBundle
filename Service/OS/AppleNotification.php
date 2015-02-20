<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Model\Device,
    DABSquared\PushNotificationsBundle\Device\Types;

use DABSquared\PushNotificationsBundle\Message\MessageStatus;
use DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface;
use DABSquared\PushNotificationsBundle\Model\MessageManagerInterface;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpKernel\Kernel;

class AppleNotification implements OSNotificationServiceInterface
{

    /**
     * Kernel
     *
     * @var Kernel
     */
    protected $kernel;

    /**
     * Array for certificates
     *
     * @var array
     */
    protected $certificates;


    /**
     * Message Manager
     *
     * @var \DABSquared\PushNotificationsBundle\Model\MessageManager
     */
    protected $messageManager;

    /**
     * Device Manager
     *
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceManager
     */
    protected $deviceManager;


    /**
     * JSON_UNESCAPED_UNICODE
     *
     * @var boolean
     */
    protected $jsonUnescapedUnicode = FALSE;


    protected $entrustPath = null;

    /**
     * Constructor
     *
     * @param Kernel $kernel
     * @param array $certificates
     * @param \DABSquared\PushNotificationsBundle\Model\MessageManagerInterface $messageManager
     * @param \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface $deviceManager
     */
    public function __construct($kernel, $certificates, MessageManagerInterface $messageManager, DeviceManagerInterface $deviceManager)
    {
        $this->kernel = $kernel;
        $this->certificates = $certificates;
        $this->messageManager = $messageManager;
        $this->deviceManager = $deviceManager;

        try {
            $this->entrustPath = $kernel->locateResource("@DABSquaredPushNotificationsBundle/Resources/public/certs/entrust_2048_ca.cer");
        } catch(\InvalidArgumentException $exception) {

        } catch(\RuntimeException $exception) {

        }
    }


    /**
     * Send a notification message
     *
     * @param MessageInterface $message
     * @return bool|void
     * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     */
    public function send(MessageInterface $message)
    {
        $this->sendMessages(array($message));
    }


    /**
     * Send a bunch of notification messages
     *
     * @param array $messages
     * @return bool|void
     * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     */
    public function sendMessages(array $messages)
    {

        /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
        foreach ($messages as $message) {
            if ($message->getTargetOS() != Types::OS_IOS) {
                throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by APN", get_class($message)));
            }

            foreach ($this->certificates as &$cert) {
                if(!array_key_exists("messages", $cert)) {
                    $cert['messages'] = array();
                }

                if($message->getDevice()->getState() == Device::STATE_SANDBOX && $cert['sandbox'] == true && $message->getDevice()->getAppId() == $cert['internal_app_id']) {
                    $cert['messages'][] = $message;
                } else if($message->getDevice()->getState() == Device::STATE_PRODUCTION && $cert['sandbox'] == false && $message->getDevice()->getAppId() == $cert['internal_app_id']) {
                    $cert['messages'][] = $message;
                }


                $apnURL = "tls://gateway.push.apple.com:2195";

                if($message->getDevice()->getState() == Device::STATE_SANDBOX) {
                    $apnURL = "tls://gateway.sandbox.push.apple.com:2195";
                }

                $cert["apnURL"] = $apnURL;
            }
        }

        if(count($this->certificates) == 0) {
            foreach ($messages as $message) {
                $message->setStatus(MessageStatus::MESSAGE_STATUS_NO_CERT);
                $this->messageManager->saveMessage($message);
            }
            throw new RuntimeException(sprintf("The device with it's current state, %s did not find a valid Push Certificate", $message->getDevice()->getState()));
        }

        foreach($this->certificates as &$cert) {
            if(!array_key_exists("messages", $cert)) {
               continue;
            }

            /** @var \DABSquared\PushNotificationsBundle\Model\MessageInterface $message */
            foreach($cert['messages'] as $message) {
                $this->writeApnStream($cert, $message);
            }
        }
    }

    /**
     * @param $cert
     */
    protected function writeApnStream($cert, \DABSquared\PushNotificationsBundle\Model\MessageInterface $message)
    {
        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $cert['pem']);
        if (strlen($cert['passphrase'])) {
            stream_context_set_option($ctx, "ssl", "passphrase", $cert['passphrase']);
        }

        if(!is_null($this->entrustPath)) {
            stream_context_set_option($ctx, 'ssl', 'cafile', $this->entrustPath);
        }


        $apns = null;

        try {
            $apns = stream_socket_client($cert["apnURL"], $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
            // Reduce buffering and blocking
            if (function_exists("stream_set_read_buffer")) {
                stream_set_read_buffer($apns, 6);
            }
            stream_set_write_buffer($apns, 0);
            stream_set_blocking($apns, 0);
        } catch (\ErrorException $er) {
            /* @var \DABSquared\PushNotificationsBundle\Model\MessageInterface $message*/
            foreach ($cert['messages'] as $message) {
                $message->setStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);
                $this->messageManager->saveMessage($message);
            }
            return;
        }


        $payload = $this->createPayload($message, $cert);

        try {
            $response = (strlen($payload) === @fwrite($apns, $payload, strlen($payload)));

            $readStreams = array($apns);
            $null = NULL;
            $streamsReadyToRead = @stream_select($readStreams, $null, $null, 1, 0);
            if ($streamsReadyToRead > 0) {
                // Unpack error response data and set as the result
                $response = @unpack("Ccommand/Cstatus/Nidentifier", fread($apns, 6));
                if (is_resource($apns)) {
                    fclose($apns);
                }

                //TODO: Set Response on the message

            }
        } catch (\ErrorException $er) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);
            $this->messageManager->saveMessage($message);
            return;
        }

        $message->setStatus(MessageStatus::MESSAGE_STATUS_SENT);
        $this->messageManager->saveMessage($message);


        if(is_resource($apns)) {
            fclose($apns);
        }
    }


    /**
     * Creates the full payload for the notification
     *
     * @param MessageInterface $message
     * @param $cert
     * @return string
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function createPayload(MessageInterface $message, $cert)
    {
        $newBadge = $message->getDevice()->getBadgeNumber()+1;

        $message->getDevice()->setBadgeNumber($newBadge);
        $this->deviceManager->saveDevice($message->getDevice());

        $messageBody = $message->getMessageBody();

        if ($cert['json_unescaped_unicode']) {
            // Validate PHP version
            if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
                throw new \LogicException(sprintf(
                    'Can\'t use JSON_UNESCAPED_UNICODE option on PHP %s. Support PHP >= 5.4.0',
                    PHP_VERSION
                ));
            }

            // WARNING:
            // Set otpion JSON_UNESCAPED_UNICODE is violation
            // of RFC 4627
            // Because required validate charsets (Must be UTF-8)

            $encoding = mb_detect_encoding($messageBody['aps']['alert']);

            if ($encoding != 'UTF-8' && $encoding != 'ASCII') {
                throw new \InvalidArgumentException(sprintf(
                    'Message must be UTF-8 encoding, "%s" given.',
                    mb_detect_encoding($messageBody['aps']['alert'])
                ));
            }

            $jsonBody = json_encode($message, JSON_UNESCAPED_UNICODE);
        }
        else {
            $jsonBody = json_encode($messageBody);
        }

        $token = preg_replace("/[^0-9A-Fa-f]/", "", $message->getDevice()->getDeviceToken());
        $payload = chr(1) . pack("N", $message->getId()) . pack("N", $message->getExpiry()) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        return $payload;
    }
}
