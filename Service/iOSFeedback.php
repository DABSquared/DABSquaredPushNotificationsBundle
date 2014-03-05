<?php

namespace DABSquared\PushNotificationsBundle\Service;

use DABSquared\PushNotificationsBundle\Device\iOS\Feedback;

class iOSFeedback
{
    /**
     * Array for certificates
     *
     * @var array
     */
    protected $certificates;

    /**
     * Constructor
     *
     * @param $certificates
     */
    public function __construct($certificates)
    {
        $this->certificates = $certificates;

    }

    public function getDeviceUUIDS($appId = null, $sandbox = null) {
        $deviceUUIDS = array();

        foreach($this->certificates as $cert) {


            //$cert['sandbox'] == true   $cert['internal_app_id']

        }
    }


    /**
     * Gets an array of device UUID unregistration details
     * from the APN feedback service
     *
     * @param $cert
     * @return array
     * @throws \RuntimeException
     */
    private function getDeviceUUIDsForCertificate($cert)
    {
        if (!strlen($this->pem)) {
            throw new \RuntimeException("PEM not provided");
        }

        $feedbackURL = "ssl://feedback.push.apple.com:2196";
        if ($this->sandbox) {
            $feedbackURL = "ssl://feedback.sandbox.push.apple.com:2196";
        }
        $data = "";

        $ctx = $this->getStreamContext();
        $fp = stream_socket_client($feedbackURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        while (!feof($fp)) {
            $data .= fread($fp, 4096);
        }
        fclose($fp);
        if (!strlen($data)) {
            return array();
        }

        $feedbacks = array();
        $items = str_split($data, 38);
        foreach ($items as $item) {
            $feedback = new Feedback();
            $feedbacks[] = $feedback->unpack($item);
        }
        return $feedbacks;
    }

    /**
     * Gets a stream context set up for SSL
     * using our PEM file and passphrase
     *
     * @return resource
     */
    protected function getStreamContext()
    {
        $ctx = stream_context_create();

        stream_context_set_option($ctx, "ssl", "local_cert", $this->pem);
        if (strlen($this->passphrase)) {
            stream_context_set_option($ctx, "ssl", "passphrase", $this->passphrase);
        }

        return $ctx;
    }

}
