<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\Message,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Device\Types,
    DABSquared\PushNotificationsBundle\Model\Device;

use Buzz\Browser;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class iOSNotification implements OSNotificationServiceInterface
{

    /**
     * Array for certificates
     *
     * @var array
     */
    protected $certificates;

    /**
     * Array for streams to APN
     *
     * @var array
     */
    protected $apnStreams;

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
     */
    public function __construct($certificates)
    {
        $this->certificates = $certificates;

    }


    /**
     * Send a notification message
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface|\DABSquared\PushNotificationsBundle\Service\OS\MessageInterface $message
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
            if($message->getDevice()->getState() == Device::STATE_SANDBOX && $cert['sandbox'] == true) {
                $certsToTry[] = $cert;
            } else if($message->getDevice()->getState() == Device::STATE_PRODUCTION && $cert['sandbox'] == false) {
                $certsToTry[] = $cert;
            }
        }

        if(count($certsToTry) == 0) {
            throw new RuntimeException(sprintf("The device with it's current state, %s did not find a valid Push Certificate", $message->getDevice()->getState()));
        }

        foreach($certsToTry as $cert) {
            $this->apnStreams = array();
            $payload = $this->createPayload($message->getDevice()->getDeviceToken(), $message->getMessageBody(), $cert);
            $result = $this->writeApnStream($apnURL, $payload ,$cert);
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
    protected function writeApnStream($apnURL, $payload, $cert)
    {

        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $cert['pem']);
        if (strlen($cert['passphrase'])) {
            stream_context_set_option($ctx, "ssl", "passphrase", $cert['passphrase']);
        }

        $apns = stream_socket_client($apnURL, $err, $errstr, 2, STREAM_CLIENT_CONNECT, $ctx);

        echo $errstr;

        fwrite($apns, $payload);
        fclose($apns);
    }


    /**
     * Creates the full payload for the notification
     *
     * @param $messageId
     * @param $token
     * @param $message
     * @return string
     */
    protected function createPayload($token, $message, $cert)
    {
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

            if (mb_detect_encoding($message['aps']['alert']) != 'UTF-8') {
                throw new \InvalidArgumentException(sprintf(
                    'Message must be UTF-8 encoding, "%s" given.',
                    mb_detect_encoding($message)
                ));
            }


            $jsonBody = json_encode($message, JSON_UNESCAPED_UNICODE ^ JSON_FORCE_OBJECT);
        }
        else {
            $jsonBody = json_encode($message, JSON_FORCE_OBJECT);
        }
        //$token = preg_replace("/[^0-9A-Fa-f]/", "", $token);
        //$payload = chr(1) . pack("N", 0) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        $payload = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $token)) . chr(0) . chr(strlen($jsonBody)) . $jsonBody;

        return $payload;
    }
}
