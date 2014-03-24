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
     * @ORM\OneToMany(targetEntity="Spark\BaseBundle\Entity\AppEvent", mappedBy="device")
     */
    protected $appEvents;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->appEvents = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * @param \DABSquared\PushNotificationsBundle\Model\AppEventInterface $appEvent
     * @return $this|void
     */
    public function addAppEvent(\DABSquared\PushNotificationsBundle\Model\AppEventInterface $appEvent)
    {
        $this->appEvents[] = $appEvent;
        return $this;
    }

    /**
     * Remove app event
     */
    public function removeAppEvent(\DABSquared\PushNotificationsBundle\Model\AppEventInterface $appEvent)
    {
        $this->appEvents->removeElement($appEvent);
    }

    /**
     * Get app events
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAppEvents()
    {
        return $this->appEvents;
    }

}

```

The Message:

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
     * Get device
     *
     * @return \Spark\BaseBundle\Entity\Device
     */
    public function getDevice()
    {
        return $this->device;
    }

}
```

And the AppEvent Class

``` php
<?php
 //src/MyProject/MyBundle/Entity/AppEvent.php

namespace MyProject\MyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DABSquared\PushNotificationsBundle\Entity\AppEvent as BaseAppEvent;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks()
 */
class AppEvent extends BaseAppEvent {

    /**
     * @ORM\ManyToOne(targetEntity="MyProject\MyBundle\Entity\Device", inversedBy="appEvents")
     */
    protected $device;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @ORM\PrePersist
     */
    public function PrePersist() {
        $this->createdAt = new \DateTime();
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
            appevent: MyProject\MyBundle\Entity\AppEvent


```

Or if you prefer XML:

``` xml
# app/config/config.xml
<dab_squared_push_notifications:config db-driver="orm">
    <dab_squared_push_notifications:class>
        <dab_squared_push_notifications:model
            device="MyProject\MyBundle\Entity\Device"
            message="MyProject\MyBundle\Entity\Message"
            appevent="MyProject\MyBundle\Entity\AppEvent"
        />
    </dab_squared_push_notifications:class>
</dab_squared_push_notifications:config>

```
### Back to the main step
[Step 2: Create your Device, Message and AppEvent classes](2-create_your_device_message_and_appevent_classes.md).
