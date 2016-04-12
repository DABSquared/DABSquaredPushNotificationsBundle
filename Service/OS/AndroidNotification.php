<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use Buzz\Browser;
use Buzz\Client\AbstractCurl;
use Buzz\Client\Curl;
use Buzz\Client\MultiCurl;
use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Device\Types;
use Psr\Log\LoggerInterface;

class AndroidNotification implements OSNotificationServiceInterface
{

    /**
     * Whether or not to use the dry run GCM
     *
     * @var bool
     */
    protected $useDryRun = false;

    /**
     * GCM endpoint
     *
     * @var string
     */
    protected $apiURL = "https://android.googleapis.com/gcm/send";

    /**
     * Array of used api keys
     *
     * @var string[]
     */
    protected $apiKeys;

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
     * Monolog logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param string       $apiKeys
     * @param bool         $useMultiCurl
     * @param int          $timeout
     * @param LoggerInterface $logger
     * @param AbstractCurl $client (optional)
     * @param bool         $dryRun
     */
    public function __construct($apiKeys, $useMultiCurl, $timeout, $logger, AbstractCurl $client = null, $dryRun = false)
    {
        $this->useDryRun = $dryRun;
        $this->apiKeys = $apiKeys;
        if (!$client) {
            $client = ($useMultiCurl ? new MultiCurl() : new Curl());
        }
        $client->setTimeout($timeout);
        $this->browser = new Browser($client);
        $this->browser->getClient()->setVerifyPeer(false);
        $this->logger = $logger;
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
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by GCM", $message->getTargetOS()));
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

        if ($this->useDryRun) {
            $data['dry_run'] = true;
        }

        $device = $message->getDevice();
        // Chunk number of registration IDs according to the maximum allowed by GCM
        $chunks = array_chunk(array($device->getDeviceToken()), $this->registrationIdMaxCount);

        // Perform the calls (in parallel)
        $this->responses = array();
        foreach ($chunks as $registrationIDs) {
            $data["registration_ids"] = $registrationIDs;
            $this->responses[] = $this->browser->post($this->apiURL, $headers, json_encode($data));
        }

        // Determine success
        foreach ($this->responses as $response) {
            $message = json_decode($response);
            if ($message === null || $message->success == 0 || $message->failure > 0) {
                if (is_null($message)) {
                    $this->logger->error($response->getContent());
                } else {
                    if (isset($message->results)) {
                        foreach ($message->results as $result) {
                            if (isset($result->error)) {
                                $this->logger->info($result->error);
                                $error = $result->error;
                                if ($error == "InvalidRegistration" || $error == "NotRegistered") {
                                    // remove devices which do not have a valid registration or are no longer registered
                                    $device->setStatus(DeviceStatus::DEVICE_STATUS_UNACTIVE);
                                }
                            }
                        }
                    } else {
                        throw new InvalidMessageTypeException("No results on Android GCM messaging");
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
