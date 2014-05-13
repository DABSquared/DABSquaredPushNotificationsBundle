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
     * @var mixed
     */
    protected $id;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceInterface
     */
    protected $device;

    /**
     * @var integer
     */
    protected $type;

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

    /********************************************************
     * iOS Specific Stuff
     ********************************************************/

    /**
     * The APS core body
     *
     * @var array
     */
    protected $apsBody = array();

    /********************************************************
     * Android Specific Stuff
     ********************************************************/

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
     * Class constructor
     */
    public function __construct($type)
    {
        $this->type = $type;

        $this->apsBody = array(
            "aps" => array(
            ),
        );

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
        $this->message = $message;
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
        $this->title = $title;
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

        if($this->type == Types::OS_IOS) {
        }else if($this->type == Types::OS_ANDROID_GCM || $this->type == Types::OS_ANDROID_C2DM) {

        }else if($this->type == Types::OS_BLACKBERRY) {
        }

        if($this->type == Types::OS_IOS) {
            $this->apsBody["aps"]["alert"] = $this->getMessage();

            if(!is_null($this->sound)) {
                $this->apsBody["aps"]["sound"] = $this->sound;
            }

            if($this->contentAvailable) {
                $this->apsBody["aps"]["content-available"] = 1;
            }

            $payloadBody = $this->apsBody;
            if (!empty($this->customData)) {
                $payloadBody = array_merge($payloadBody, $this->customData);
            }
            return $payloadBody;
        } else if($this->type == Types::OS_SAFARI) {
            $this->apsBody["aps"]["alert"] = array();
            $this->apsBody["aps"]["alert"]['body'] = $this->getMessage();
            $this->apsBody["aps"]["alert"]['title'] = $this->getTitle();
            $this->apsBody["aps"]["alert"]['action'] = 'View';

            $payloadBody = $this->apsBody;
            if (!empty($this->customData)) {
                $payloadBody = array_merge($payloadBody, $this->customData);
            }
            return $payloadBody;
        } else if($this->type == Types::OS_ANDROID_GCM || $this->type == Types::OS_ANDROID_C2DM) {
            $data = array(
                "registration_id" => $this->device->getDeviceidentifier(),
                "collapse_key"    => $this->collapseKey,
                "data.message"    => $this->message,
                "data.content_available" => $this->contentAvailable ? 1 : 0,
            );
            if (!empty($this->customData)) {
                $data = array_merge($data, $this->customData);
            }
            return $data;
        } else if($this->type == Types::OS_BLACKBERRY) {
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
        if($this->type == Types::OS_IOS) {
            if (!is_array($data)) {
                throw new \InvalidArgumentException(sprintf('Messages custom data must be array, "%s" given.', gettype($data)));
            }

            if (array_key_exists("aps", $data)) {
                unset($data["aps"]);
            }

            foreach ($data as $key => $value) {
                $this->addCustomData($key, $value);
            }
        } else if($this->type == Types::OS_SAFARI) {
            if (!is_array($data)) {
                throw new \InvalidArgumentException(sprintf('Messages custom data must be array, "%s" given.', gettype($data)));
            }

            if (array_key_exists("aps", $data)) {
                unset($data["aps"]);
            }

            foreach ($data as $key => $value) {
                $this->addCustomData($key, $value);
            }
        } else if($this->type == Types::OS_ANDROID_GCM || $this->type == Types::OS_ANDROID_C2DM) {
            $this->customData = (is_array($data) ? $data : array($data));
        } else if($this->type == Types::OS_BLACKBERRY) {
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
        if($this->type == Types::OS_IOS) {
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
     * @param $type
     */
    public function setTargetOS($type)
    {
        $this->type = $type;
    }

    /**
     * Returns the target OS for this message
     * @return string
     */
    public function getTargetOS()
    {
       if($this->type == Types::OS_IOS) {
           return  Types::OS_IOS;
       }else  if($this->type == Types::OS_SAFARI) {
           return  Types::OS_SAFARI;
       }else  if($this->type == Types::OS_ANDROID_C2DM) {
           return Types::OS_ANDROID_C2DM;
       }else  if($this->type == Types::OS_ANDROID_GCM) {
           return Types::OS_ANDROID_GCM;
       }else if($this->type == Types::OS_BLACKBERRY) {
            return Types::OS_BLACKBERRY;
       }
        return null;
    }


    /***************************************
     * Android Specific
     ***************************************/

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

    /**
     * @param array $apsBody
     */
    public function setApsBody($apsBody)
    {
        $this->apsBody = $apsBody;
    }

    /**
     * @return array
     */
    public function getApsBody()
    {
        return $this->apsBody;
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
     * @param \DABSquared\PushNotificationsBundle\Model\Device $device
     */
    public function setDevice(\DABSquared\PushNotificationsBundle\Model\Device $device)
    {
        $this->device = $device;
        $this->setTargetOS($device->getType());
    }

    /**
     * @return \DABSquared\PushNotificationsBundle\Model\Device
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

}
