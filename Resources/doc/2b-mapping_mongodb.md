Step 2b: Setup MongoDB mapping
==============================
The MongoDB implementation does not provide a concrete Device class for your use,
you must create one:

``` php
<?php
// src/MyProject/MyBundle/Document/Device.php

namespace MyProject\MyBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use DABSquared\PushNotificationsBundle\Document\Device as BaseDevice;

/**
 * @MongoDB\Document
 * @MongoDB\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Device extends BaseDevice
{
    /**
     * @MongoDB\Id
     */
    protected $id;


}
```

Additionally, create the Message class:

``` php
<?php
// src/MyProject/MyBundle/Document/Message.php

namespace MyProject\MyBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use FOS\CommentBundle\Document\Message as BaseMessage;

/**
 * @MongoDB\Document
 * @MongoDB\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Message extends BaseMessage
{
   /**
     * Device of this message
     *
     * @var Device
     * @MongoDB\ReferenceOne(targetDocument="MyProject\MyBundle\Document\Device")
     */
    protected $device;

}
```

## Configure your application

In YAML:

``` yaml
# app/config/config.yml

fos_comment:
    db_driver: mongodb
    class:
        model:
            device: MyProject\MyBundle\Document\Device
            message: MyProject\MyBundle\Document\Message

```

Or if you prefer XML:

``` xml
# app/config/config.xml

<dab_squared_push_notifications:config db-driver="mongodb">
    <dab_squared_push_notifications:class>
        <dab_squared_push_notifications:model
            device="MyProject\MyBundle\Document\Device"
            message="MyProject\MyBundle\Document\Message"
        />
    </dab_squared_push_notifications:class>
</dab_squared_push_notifications:config>

```

### Back to the main step
[Step 2: Create your Device and Message classes](2-create_your_device_and_message_classes.md).
