Step 1: Setting up the bundle
=============================
### A) Download and install DABSquaredPushNotificationsBundle

To install DABSquaredPushNotificationsBundle run the following command
``` php
{
    "require": {
        "dabsquared/dabsquared-push-notifications-bundle": "dev-master"
    }
}
```
### B) Enable the bundle

Finally, enable the required bundles in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
            new DABSquared\PushNotificationsBundle\DABSquaredPushNotificationsBundle(),
    );
}
```

### Continue to the next step!
When you're done. Continue by creating the appropriate Device and Message classes:
[Step 2: Create your Device and Message classes](2-create_your_device_message_and_appevent_classes.md).
