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
        if (!$message->isGCM()) {
            throw new InvalidMessageTypeException("Non-GCM messages not supported by the Android GCM sender");
        }

        $headers = array(
            "Authorization: key=" . $this->apiKey,
            "Content-Type: application/json",
        );
        $data = array_merge(
            $message->getGCMOptions(),
            array("data" => $message->getData())
        );

        // Chunk number of registration IDs according to the maximum allowed by GCM
        $chunks = array_chunk($message->getGCMIdentifiers(), $this->registrationIdMaxCount);

        // Perform the calls (in parallel)
        $this->responses = array();
        foreach ($chunks as $registrationIDs) {
            $data["registration_ids"] = $registrationIDs;
            $this->responses[] = $this->browser->post($this->apiURL, $headers, json_encode($data));
        }
        $this->browser->getClient()->flush();

        // Determine success
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
}
