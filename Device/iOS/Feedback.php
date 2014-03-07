<?php

namespace DABSquared\PushNotificationsBundle\Device\iOS;

class Feedback
{
    /** @var  string */
    public $timestamp;

    /** @var  string */
    public $tokenLength;

    /** @var  string */
    public $uuid;

    /** @var  boolean */
    public $isSandbox;

    /** @var  string */
    public $internalAppId;


    public function __construct($isSandbox, $internalAppId) {
        $this->isSandbox = $isSandbox;
        $this->internalAppId = $internalAppId;
    }

    /**
     * Unpacks the APNS data into the required fields
     *
     * @param $data
     * @return \DABSquared\PushNotificationsBundle\Device\iOS\Feedback
     */
    public function unpack($data)
    {
        $token = unpack("N1timestamp/n1length/H*token", $data);
        $this->timestamp = $token["timestamp"];
        $this->tokenLength = $token["length"];
        $this->uuid = $token["token"];

        return $this;
    }
}
