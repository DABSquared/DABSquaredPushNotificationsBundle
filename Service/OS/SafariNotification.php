<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\Message,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Device\Types,
    DABSquared\PushNotificationsBundle\Model\Device;

use Buzz\Browser;
use DABSquared\PushNotificationsBundle\Message\MessageStatus;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class SafariNotification implements OSNotificationServiceInterface
{

    /**
     *
     */
    protected $pem;

    /**
     *
     */
    protected $passphrase;


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

    /**
     * Constructor
     *
     * @param $sandbox
     * @param $pem
     * @param $passphrase
     *
     */
    public function __construct($pem, $passphrase, \DABSquared\PushNotificationsBundle\Model\MessageManager $messageManager, \DABSquared\PushNotificationsBundle\Model\DeviceManager $deviceManager)
    {
        $this->pem = $pem;
        $this->passphrase = $passphrase;
        $this->messageManager = $messageManager;
        $this->deviceManager = $deviceManager;
    }


    /**
     * Send a notification message
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface $message
     * @throws \RuntimeException
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return int
     */
    public function send(MessageInterface $message)
    {
        if ($message->getTargetOS() != Types::OS_IOS) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by APN", get_class($message)));
        }

        $apnURL = "ssl://gateway.push.apple.com:2195";

        if($message->getDevice()->getState() == Device::STATE_SANDBOX) {
            $apnURL = "ssl://gateway.sandbox.push.apple.com:2195";
        }

        $certsToTry = array();

        foreach ($this->certificates as $cert) {
            if($message->getDevice()->getState() == Device::STATE_SANDBOX && $cert['sandbox'] == true && $message->getDevice()->getAppId() == $cert['internal_app_id']) {
                $certsToTry[] = $cert;
            } else if($message->getDevice()->getState() == Device::STATE_PRODUCTION && $cert['sandbox'] == false && $message->getDevice()->getAppId() == $cert['internal_app_id']) {
                $certsToTry[] = $cert;
            }
        }

        if(count($certsToTry) == 0) {
            throw new RuntimeException(sprintf("The device with it's current state, %s did not find a valid Push Certificate", $message->getDevice()->getState()));
        }

        foreach($certsToTry as $cert) {
            $payload = $this->createPayload($message, $cert);
            $result = $this->writeApnStream($apnURL, $payload ,$cert, $message);
        }

    }


    /**
     * Send a bunch of notification messages
     *
     * @param array $messages
     * @throws \RuntimeException
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return int
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

                if(!array_key_exists("payloads", $cert)) {
                    $cert['payloads'] = array();
                }

                if($message->getDevice()->getState() == Device::STATE_SANDBOX && $cert['sandbox'] == true && $message->getDevice()->getAppId() == $cert['internal_app_id']) {
                    $cert['messages'][] = $message;
                    $cert['payloads'][] = $this->createPayload($message, $cert);
                } else if($message->getDevice()->getState() == Device::STATE_PRODUCTION && $cert['sandbox'] == false && $message->getDevice()->getAppId() == $cert['internal_app_id']) {
                    $cert['messages'][] = $message;
                    $cert['payloads'][] = $this->createPayload($message, $cert);
                }


                $apnURL = "ssl://gateway.push.apple.com:2195";

                if($message->getDevice()->getState() == Device::STATE_SANDBOX) {
                    $apnURL = "ssl://gateway.sandbox.push.apple.com:2195";
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


            $result = $this->writeApnStreamMessages($cert["apnURL"], $cert, $cert['payloads'] , $cert['messages']);
        }
    }

    /**
     * Write data to the apn stream that is associated with the given apn URL
     *
     * @param string $apnURL
     * @param string $payload
     * @throws \RuntimeException
     * @return mixed
     */
    protected function writeApnStreamMessages($apnURL, $cert,array $payloads, array $messages)
    {

        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $cert['pem']);
        if (strlen($cert['passphrase'])) {
            stream_context_set_option($ctx, "ssl", "passphrase", $cert['passphrase']);
        }



        try {
            $apns = stream_socket_client($apnURL, $err, $errstr, 2, STREAM_CLIENT_CONNECT, $ctx);
        } catch (\ErrorException $er) {
            /* @var \DABSquared\PushNotificationsBundle\Model\Message $message */
            foreach ($messages as $message) {
                $message->setStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);
                $this->messageManager->saveMessage($message);
            }
            return;
        }




        $i = 0;
        foreach ($payloads as $payload) {

            try {
                fwrite($apns, $payload);
            } catch (\ErrorException $er) {
                $messages[$i]->setStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);
                $this->messageManager->saveMessage($messages[$i]);
                $i++;
                continue;
            }

            $messages[$i]->setStatus(MessageStatus::MESSAGE_STATUS_SENT);

            $this->messageManager->saveMessage($messages[$i]);

            $i++;
        }

        fclose($apns);
    }


    /**
     * Write data to the apn stream that is associated with the given apn URL
     *
     * @param string $apnURL
     * @param string $payload
     * @throws \RuntimeException
     * @return mixed
     */
    protected function writeApnStream($apnURL, $payload, $cert, \DABSquared\PushNotificationsBundle\Model\MessageInterface  $message)
    {

        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $cert['pem']);
        if (strlen($cert['passphrase'])) {
            stream_context_set_option($ctx, "ssl", "passphrase", $cert['passphrase']);
        }

        try {
            $apns = stream_socket_client($apnURL, $err, $errstr, 2, STREAM_CLIENT_CONNECT, $ctx);
            fwrite($apns, $payload);
            fclose($apns);
        } catch (\ErrorException $er) {
            $message->setStatus(MessageStatus::MESSAGE_STATUS_STREAM_ERROR);
            $this->messageManager->saveMessage($message);
            return;
        }


        $message->setStatus(MessageStatus::MESSAGE_STATUS_SENT);
        $this->messageManager->saveMessage($message);

    }


    /**
     * Creates the full payload for the notification
     *
     * @param $messageId
     * @param $token
     * @param $message /DABSquared/PushNotificationsBundle/Model/Message
     * @return string
     */
    protected function createPayload(\DABSquared\PushNotificationsBundle\Model\Message $message, $cert)
    {
        $messageBody = $message->getMessageBody();
        $newBadge = $message->getDevice()->getBadgeNumber()+1;

        $message->getDevice()->setBadgeNumber($newBadge);
        $this->deviceManager->saveDevice($message->getDevice());
        $messageBody["aps"]["badge"] = $message->getDevice()->getBadgeNumber();

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

            if (mb_detect_encoding($messageBody['aps']['alert']) != 'UTF-8') {
                throw new \InvalidArgumentException(sprintf(
                    'Message must be UTF-8 encoding, "%s" given.',
                    mb_detect_encoding($messageBody)
                ));
            }


            $jsonBody = json_encode($messageBody, JSON_UNESCAPED_UNICODE ^ JSON_FORCE_OBJECT);
        }
        else {
            $jsonBody = json_encode($messageBody, JSON_FORCE_OBJECT);
        }
        //$token = preg_replace("/[^0-9A-Fa-f]/", "", $token);
        //$payload = chr(1) . pack("N", 0) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        $payload = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $message->getDevice()->getDeviceToken())) . chr(0) . chr(strlen($jsonBody)) . $jsonBody;
        var_dump($payload);

        return $payload;
    }
}
