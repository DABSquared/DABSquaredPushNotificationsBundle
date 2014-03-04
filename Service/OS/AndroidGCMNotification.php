<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\Message,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Device\Types;
use Buzz\Browser,
    Buzz\Client\MultiCurl;

class AndroidGCMNotification implements OSNotificationServiceInterface
{
    /**
     * GCM endpoint
     *
     * @var string
     */
    protected $apiURL = "https://android.googleapis.com/gcm/send";

    /**
     * Google GCM API key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Max registration count
     *
     * @var integer
     */
    protected $registrationIdMaxCount = 1000;

    /**
     * Browser object
     *
     * @var \Buzz\Browser
     */
    protected $browser;

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
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->browser = new Browser(new MultiCurl());
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

        $headers = array(
            "Authorization: key=" . $this->apiKey,
            "Content-Type: application/json",
        );
        $data = array_merge(
            $message->getGCMOptions(),
            array("data" => $message->getMessageBody())
        );

        $device = $message->getDevice();
        // Chunk number of registration IDs according to the maximum allowed by GCM
        $chunks = array_chunk(array($device->getDeviceIdentifier()), $this->registrationIdMaxCount);

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
            $result = curl_exec($ch);
            //TODO: Parse the response
            // Close connection
            curl_close($ch);
        }

        // Determine success
        //TODO: Parse the response
        foreach ($this->responses as $response) {
            $message = json_decode($response->getContent());
            if ($message === null || $message->success == 0 || $message->failure > 0) {
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
