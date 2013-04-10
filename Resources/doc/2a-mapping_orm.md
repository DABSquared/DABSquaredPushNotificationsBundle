Step 2a: Setup Doctrine ORM mapping
===================================
The ORM implementation does not provide a concrete Device class for your use,
you must create one. This can be done by extending the abstract entities
provided by the bundle and creating the appropriate mappings.

For example:

``` php
<?php
 //src/MyProject/MyBundle/Entity/Device.php

namespace MyProject/MyBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use DABSquared\PushNotificationsBundle\Entity\Device as BaseDevice;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks()
 */
class Device extends BaseDevice {

    /**
     * @ORM\OneToMany(targetEntity="MyProject\MyBundle\Entity\Message", mappedBy="device")
     */
    protected $messages;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function PrePersist() {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

    }

    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate() {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Add message
     * @return Device
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * Remove message
     */
    public function removeMessage($message)
    {
        $this->messages->removeElement($message);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMessages()
    {
        return $this->messages;
    }
}

```

And the Message:

``` php
<?php
 //src/MyProject/MyBundle/Entity/Message.php

namespace MyProject\MyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DABSquared\PushNotificationsBundle\Entity\Message as BaseMessage;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks()
 */
class Message extends BaseMessage {

    public function __construct() {

    }

    /**
     * @ORM\PrePersist
     */
    public function PrePersist() {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

    }

    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate() {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\ManyToOne(targetEntity="MyProject\MyBundle\Entity\Device", inversedBy="messages")
     */
    protected $device;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set device
     * @return Message
     */
    public function setDevice($device = null)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device
     *
     * @return \DABSquared\PushBundle\Entity\Device
     */
    public function getDevice()
    {
        return $this->device;
    }
}
```

## Configure your application

``` yaml
# app/config/config.yml

dab_squared_push_notifications:
    db_driver: orm
    class:
        model:
            device: MyProject\MyBundle\Entity\Device
            message: MyProject\MyBundle\Entity\Message

```

Or if you prefer XML:

``` xml
# app/config/config.xml
<dab_squared_push_notifications:config db-driver="orm">
    <dab_squared_push_notifications:class>
        <dab_squared_push_notifications:model
            device="MyProject\MyBundle\Entity\Device"
            message="MyProject\MyBundle\Entity\Message"
        />
    </dab_squared_push_notifications:class>
</dab_squared_push_notifications:config>

```
### Back to the main step
[Step 2: Create your Device and Message classes](2-create_your_device_and_message_classes.md).
