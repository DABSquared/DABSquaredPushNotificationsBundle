<?php

namespace DABSquared\PushNotificationsBundle\Model;

use DABSquared\PushNotificationsBundle\Device\Types;


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
     * @var mixed
     */
    protected $device;

    /**
     * @var integer
     */
    protected $type;

    /**
     * String message
     *
     * @var string
     */
    protected $message = "";

    protected $badgeNumber = 0;

    /**
     * Custom data
     *
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
     * @var int
     */
    protected $collapseKey = self::DEFAULT_COLLAPSE_KEY;

    /**
     * Whether this is a GCM message
     *
     * @var bool
     */
    protected $isGCM = false;


    /**
     * Options for GCM messages
     *
     * @var array
     */
    protected $gcmOptions = array();



    protected $createdAt;


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

        $this->createdAt = new DateTime();
    }


    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the creation date
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Sets the message. For iOS, this is the APS alert message
     *
     * @param $message
     */
    public function setMessage($message)
    {

        $this->message = $message;

        if($this->type == Types::OS_IOS) {
            $this->apsBody["aps"]["alert"] = $message;
        }else if($this->type == Types::OS_ANDROID_GCM || $this->type == Types::OS_ANDROID_C2DM) {

        }else if($this->type == Types::OS_BLACKBERRY) {
            $this->setData($message);
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
     * Gets the full message body to send to APN
     *
     * @return array
     */
    public function getMessageBody()
    {
        if($this->type == Types::OS_IOS) {
            $payloadBody = $this->apsBody;
            if (!empty($this->customData)) {
                $payloadBody = array_merge($payloadBody, $this->customData);
            }
            return $payloadBody;
        }else if($this->type == Types::OS_ANDROID_GCM || $this->type == Types::OS_ANDROID_C2DM) {
            $data = array(
                "registration_id" => $this->identifier,
                "collapse_key"    => $this->collapseKey,
                "data.message"    => $this->message,
            );
            if (!empty($this->customData)) {
                $data = array_merge($data, $this->customData);
            }
            return $data;
        }else if($this->type == Types::OS_BLACKBERRY) {
            return $this->customData;
        }

    }

    /**
     * Sets any custom data for the APS body
     *
     * @param array $data
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
            return $this;

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
     * Add custom data
     *
     * @param string $key
     * @param mixed $value
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

            return $this;
        }

    }


    /**
     * @param $number
     * @return mixed
     */
    public function setBadgeNumber($number) {
        if($this->type == Types::OS_IOS) {
            $this->apsBody["aps"]["badge"] = $number;
        }

    }

    public function setSound($sound)
    {
        if($this->type == Types::OS_IOS) {
            $this->apsBody["aps"]["sound"] = $sound;
        }
    }

    public function setTargetOS($type) {
        $this->type = $type;
    }

    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
       if($this->type == Types::OS_IOS) {
           return  Types::OS_IOS;
       }else  if($this->type == Types::OS_ANDROID_GCM || $this->type == Types::OS_ANDROID_C2DM) {
           return ($this->isGCM ? Types::OS_ANDROID_GCM : Types::OS_ANDROID_C2DM);
       }else if($this->type == Types::OS_BLACKBERRY) {
            return Types::OS_BLACKBERRY;
       }
    }


    /***************************************
     * Android Specific
     ***************************************/

    /**
     * Android-specific
     * Returns the collapse key
     *
     * @return int
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
     * Set whether this is a GCM message
     * (default false)
     *
     * @param $gcm
     */
    public function setGCM($gcm)
    {
        $this->isGCM = !!$gcm;
    }

    /**
     * Returns whether this is a GCM message
     *
     * @return mixed
     */
    public function isGCM()
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
     *
     * @return array
     */
    public function getGCMOptions()
    {
        return $this->gcmOptions;
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
     * @param mixed $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return mixed
     */
    public function getDevice()
    {
        return $this->device;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


}
