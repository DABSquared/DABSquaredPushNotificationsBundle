Step 6: Integration with FOSUserBundle
======================================
By default, devices are created anonymously.
[FOSUserBundle](http://github.com/FriendsOfSymfony/FOSUserBundle)
authentication can be used to sign the comments.

### A) Setup FOSUserBundle
First you have to setup [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle). Check the [instructions](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.md).

### B) Extend the Device class
In order to add an owner to a device, the Device class should implement the
`UserDeviceInterface` and add a field to your mapping.

For example in the ORM:

``` php
<?php
// src/MyProject/MyBundle/Entity/Device.php

namespace MyProject\MyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\CommentBundle\Entity\Comment as BaseComment;
use FOS\CommentBundle\Model\SignedCommentInterface;
use Symfony\Component\Security\Core\User\UserInterface;

namespace DABSquared\PushBundle\Entity;


use DABSquared\PushNotificationsBundle\Entity\Device as BaseDevice;
use DABSquared\PushNotificationsBundle\Model\UserDeviceInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Device extends BaseDevice implements UserDeviceInterface
    // .. fields

    /**
     * Owner of the device
     *
     * @ORM\ManyToOne(targetEntity="MyProject\MyBundle\Entity\User")
     * @var User
     */
    protected $user;

    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
```

[Step 5: Integration with iOS](4-integration_with_ios.md).
