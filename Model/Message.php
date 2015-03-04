<?php

namespace DABSquared\PushNotificationsBundle\Model;

use DABSquared\PushNotificationsBundle\Device\Types;
use DABSquared\PushNotificationsBundle\Message\MessageStatus;


/**
 * Created by JetBrains PhpStorm.
 * User: Daniel
 * Date: 2/10/13
 * Time: 12:30 PM
 * To change this template use File | Settings | File Templates.
 */
abstract class Message implements MessageInterface
{

    /**
     * Message database id
     *
     * @var int
     */
    protected $id;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    protected $device;

    /**
     * @var int
     */
    protected $badge;

    /**
     * @var string
     */
    protected $status = MessageStatus::MESSAGE_STATUS_NOT_SENT;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var string
     */
    protected $sound = null;

    /**
     * @var boolean
     */
    protected $contentAvailable = false;

    /**
     * @var boolean
     */
    protected $isGCM = false;

    /**
     * @var array
     */
    protected $customData = array();

    /**
     * @var string
     */
    protected $urlArgs = null;

    /**
     * Collapse key for data
     *
     * @var string
     */
    protected $collapseKey = self::DEFAULT_COLLAPSE_KEY;

    /**
     * Options for GCM messages
     *
     * @var array
     */
    protected $gcmOptions = array();

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * Expiration date (UTC)
     *
     * A fixed UNIX epoch date expressed in seconds (UTC) that identifies when the notification is no longer valid and can be discarded.
     * If the expiry value is non-zero, APNs tries to deliver the notification at least once.
     * Specify zero to request that APNs not store the notification at all.
     *
     * @var int
     */
    protected $expiry = 604800;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the creation date
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the message. For iOS, this is the APS alert message
     *
     * @param $message
     */
    public function setMessage($message)
    {
        if(!is_null($message)) {
            $this->message = $message;
        }
    }


    /**
     * Returns the string message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * @param $title
     */
    public function setTitle($title)
    {
        if(!is_null($title)) {
            $this->title = $title;
        }
    }


    /**
     * Returns the string title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Gets the full message body to send to APN
     *
     * @return array
     */
    public function getMessageBody()
    {

        if($this->getTargetOS() == Types::OS_IOS || $this->getTargetOS() == Types::OS_MAC) {
            $apsBody = array();
            $apsBody["aps"] = array();

            $apsBody["aps"]["alert"] = $this->getMessage();

            if(!is_null($this->getSound())) {
                $apsBody["aps"]["sound"] = $this->getSound();
            }

            if(!is_null($this->getContentAvailable())) {
                $apsBody["aps"]["content-available"] = $this->getContentAvailable();
            }

            if(!is_null($this->getBadge())) {
                $apsBody["aps"]["badge"] = $this->getBadge();
            }

            $payloadBody = $apsBody;
            if (!empty($this->customData)) {
                $payloadBody = array_replace_recursive($payloadBody, $this->customData);
            }
            return $payloadBody;
        } else if($this->getTargetOS() == Types::OS_SAFARI) {
            $apsBody = array();
            $apsBody["aps"] = array();
            $apsBody["aps"]["alert"] = array();
            $apsBody["aps"]["alert"]['body'] = $this->getMessage();
            $apsBody["aps"]["alert"]['title'] = $this->getTitle();
            $apsBody["aps"]["alert"]['action'] = 'View';

            if(is_null($this->getURLArgs())) {
                $apsBody["aps"]['url-args'] = array("");
            } else {
                $apsBody["aps"]['url-args'] = $this->getURLArgs();
            }

            $payloadBody = $apsBody;
            if (!empty($this->customData)) {
                $payloadBody = array_replace_recursive($payloadBody, $this->customData);
            }
            return $payloadBody;
        } else if($this->getTargetOS() == Types::OS_ANDROID_GCM || $this->getTargetOS() == Types::OS_ANDROID_C2DM) {
            $data = array(
                "registration_id" => $this->device->getDeviceidentifier(),
                "collapse_key"    => $this->collapseKey,
                "data.message"    => $this->message,
                "data.content_available" => $this->contentAvailable ? 1 : 0,
            );
            if (!empty($this->customData)) {
                $data = array_replace_recursive($data, $this->customData);
            }
            return $data;
        } else if($this->getTargetOS() == Types::OS_BLACKBERRY) {
            $this->setData($this->getMessage());
            return $this->customData;
        }
        return array();
    }

    /**
     *  Sets any custom data for the message
     * @param $data
     * @throws \InvalidArgumentException
     */
    public function setData($data)
    {
        if($this->getTargetOS() == Types::OS_IOS || $this->getTargetOS() == Types::OS_MAC || $this->getTargetOS() == Types::OS_SAFARI) {
            if (!is_array($data)) {
                throw new \InvalidArgumentException(sprintf('Messages custom data must be array, "%s" given.', gettype($data)));
            }

            if (array_key_exists("aps", $data)) {
                unset($data["aps"]);
            }

            foreach ($data as $key => $value) {
                $this->addCustomData($key, $value);
            }
        } else if($this->getTargetOS() == Types::OS_ANDROID_GCM || $this->getTargetOS() == Types::OS_ANDROID_C2DM) {
            $this->customData = (is_array($data) ? $data : array($data));
        } else if($this->getTargetOS() == Types::OS_BLACKBERRY) {
             $this->customData = $data;
        }
    }

    /**
     * Returns any custom data
     *
     * @return array
     */
    public function getData()
    {
        return $this->customData;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function addCustomData($key, $value)
    {
        if($this->getTargetOS() == Types::OS_IOS || $this->getTargetOS() == Types::OS_MAC) {
            if ($key == 'aps') {
                throw new \LogicException('Can\'t replace "aps" data. Please call to setMessage, if your want replace message text.');
            }

            if (is_object($value)) {
                if (interface_exists('JsonSerializable') && !$value instanceof \stdClass && !$value instanceof \JsonSerializable) {
                    throw new \InvalidArgumentException(sprintf(
                        'Object %s::%s must be implements JsonSerializable interface for next serialize data.',
                        get_class($value), spl_object_hash($value)
                    ));
                }
            }
            $this->customData[$key] = $value;
        }
    }

    /**
     * @param string $sound
     */
    public function setSound($sound)
    {
        $this->sound = $sound;
    }

    /**
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * @param bool $contentAvailable
     */
    public function setContentAvailable($contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
    }

    /**
     * @return bool
     */
    public function getContentAvailable()
    {
        return $this->contentAvailable;
    }

    /**
     * Returns the target OS for this message
     * @return string
     */
    public function getTargetOS()
    {
        return $this->getDevice()->getType();
    }

    /**
     * @param array $customData
     */
    public function setCustomData($customData)
    {
        $this->customData = $customData;
    }

    /**
     * @return array
     */
    public function getCustomData()
    {
        return $this->customData;
    }

    /**
     * @param \DABSquared\PushNotificationsBundle\Model\DeviceInterface $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $expiry
     */
    public function setExpiry($expiry) {
        $this->expiry = $expiry;
    }

    /**
     * @return int
     */
    public function getExpiry() {
        return $this->expiry;
    }


    /**
     * @return int
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param int $badge
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;
    }

    /**
     * @return string
     */
    public function getURLArgs()
    {
        return $this->urlArgs;
    }

    /**
     * @param string $urlArgs
     */
    public function setURLArgs($urlArgs)
    {
        $this->urlArgs = $urlArgs;
    }


    #region "Android Specific"

    /**
     * Android-specific
     * Returns the collapse key
     *
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * Android-specific
     * Sets the collapse key
     *
     * @param $collapseKey
     */
    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
    }

    /**
     * @param boolean $isGCM
     */
    public function setIsGCM($isGCM)
    {
        $this->isGCM = $isGCM;
    }

    /**
     * @return boolean
     */
    public function getIsGCM()
    {
        return $this->isGCM;
    }


    /**
     * Sets GCM options
     * @param array $options
     */
    public function setGCMOptions($options)
    {
        $this->gcmOptions = $options;
    }

    /**
     * Returns GCM options
     * @return array
     */
    public function getGCMOptions()
    {
        return $this->gcmOptions;
    }

    #endregion
}
