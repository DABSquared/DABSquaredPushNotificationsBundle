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

    public function getDeviceFeedback($appId = null, $sandbox = null)
    {
        $feedbacks = array();

        foreach ($this->certificates as $cert) {
            $isSandbox = $cert['sandbox'];
            $internalAppId = $cert['internal_app_id'];

            if (!is_null($appId)) {
                if ($appId != $internalAppId) {
                    continue;
                }
            }

            if (!is_null($sandbox)) {
                if ($sandbox) {
                    if ($sandbox && !$isSandbox) {
                        continue;
                    }
                } else {
                    if (!$sandbox && $isSandbox) {
                        continue;
                    }
                }
            }

            $certFeedbacks = $this->getDeviceFeedbackCertificate($cert);
            $feedbacks = array_merge($feedbacks, $certFeedbacks);
        }
        return $feedbacks;
    }


    /**
     * Gets an array of device UUID unregistration details
     * from the APN feedback service
     *
     * @param $cert
     * @return array
     * @throws \RuntimeException
     */
    private function getDeviceFeedbackCertificate($cert)
    {
        $isSandbox = $cert['sandbox'];
        $internalAppId = $cert['internal_app_id'];
        $pem = $cert['pem'];

        if (!strlen($pem)) {
            throw new \RuntimeException("PEM not provided");
        }

        $feedbackURL = "ssl://feedback.push.apple.com:2196";
        if ($isSandbox) {
            $feedbackURL = "ssl://feedback.sandbox.push.apple.com:2196";
        }
        $data = "";

        $ctx = $this->getStreamContext($cert);
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
            $feedback = new Feedback($isSandbox, $internalAppId);
            $feedbacks[] = $feedback->unpack($item);
        }
        return $feedbacks;
    }

    /**
     * Gets a stream context set up for SSL
     * using our PEM file and passphrase
     *
     * @param $cert
     * @return resource
     */
    protected function getStreamContext($cert)
    {
        $pem = $cert['pem'];
        $passphrase = $cert['passphrase'];

        $ctx = stream_context_create();

        stream_context_set_option($ctx, "ssl", "local_cert", $pem);
        if (strlen($passphrase)) {
            stream_context_set_option($ctx, "ssl", "passphrase", $passphrase);
        }

        return $ctx;
    }

}
