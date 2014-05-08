<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Device\Types;

class AndroidGCMNotification implements OSNotificationServiceInterface
{
    /**
     * GCM endpoint
     *
     * @var string
     */
    protected $apiURL = "https://android.googleapis.com/gcm/send";

    /**
     * Array of used api keys
     *
     * @var string
     */
    protected $apiKeys;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceManager
     */
    protected $deviceManager;

    /**
     * Max registration count
     *
     * @var integer
     */
    protected $registrationIdMaxCount = 1000;

    /**
     * Collection of the responses from the GCM communication
     *
     * @var array
     */
    protected $responses;

    /**
     * Constructor
     *
     * @param $apiKey
     */
    public function __construct($apiKeys, \DABSquared\PushNotificationsBundle\Model\DeviceManager $deviceManager)
    {
        $this->apiKeys = $apiKeys;
        $this->deviceManager = $deviceManager;
    }

    /**
     * Sends the data to the given registration IDs via the GCM server
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface $message
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if ($message->getTargetOS() != Types::OS_ANDROID_GCM) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by GCM", get_class($message)));
        }

        $apiKey = null;

        foreach($this->apiKeys as $anAPIKey) {
            if($message->getDevice()->getAppId() == $anAPIKey['internal_app_id']) {
                $apiKey = $anAPIKey['api_key'];
                break;
            }
        }


        $headers = array(
            "Authorization: key=" . $apiKey,
            "Content-Type: application/json",
        );

        $data = array_merge(
            $message->getGCMOptions(),
            array("data" => $message->getMessageBody())
        );

        $device = $message->getDevice();
        // Chunk number of registration IDs according to the maximum allowed by GCM
        $chunks = array_chunk(array($device->getDeviceToken()), $this->registrationIdMaxCount);

        // Perform the calls (in parallel)
        $this->responses = array();
        foreach ($chunks as $registrationIDs) {
            $data["registration_ids"] = $registrationIDs;

            // Open connection
            $ch = curl_init();
            // Set the url, number of POST vars, POST data
            curl_setopt( $ch, CURLOPT_URL, $this->apiURL );

            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );

            // Execute post
            $this->responses[] = curl_exec($ch);
            // Close connection
            curl_close($ch);
        }

        // Determine success
        foreach ($this->responses as $response) {
            $message = json_decode($response);
            if ($message === null || $message->success == 0 || $message->failure > 0) {
                if (count($message->results)) {
                    foreach ($message->results as $result) {
                        $error = $result->error;
                        if ($error == "InvalidRegistration" || $error == "NotRegistered") {
                            // remove devices which do not have a valid registration or are no longer registered
                            $device->setStatus(DeviceStatus::DEVICE_STATUS_UNACTIVE);
                        }
                     }
                 }
                return false;
            }
        }

        return true;
    }

    /**
     * Returns responses
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    public function sendMessages(array $messages){
        foreach($messages as $message) {
            $this->send($message);
        }
    }
}
