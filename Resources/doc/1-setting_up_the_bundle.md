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

Enable the required bundles in the kernel:

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

### C) Add the configuration to the config.yml

Enable the required bundles in the kernel:

``` yml
dab_squared_push_notifications:
    db_driver: orm
    user_entity_namespace: YourProjectBundle:User
    class:
        model:
            device: YourProject\Bundle\Entity\Device
            message: YourProject\Bundle\Entity\Message
    apps:
        your_app_free: {name: 'Your App Free', internal_app_id: 0000001 }
        your_app_premium: {name: 'Your App Premium', internal_app_id: 0000002 }
    ios:
        certificates:
            dev_free: { sandbox: true, pem: %kernel.root_dir%/../pushcerts/dev/certificate.pem, passphrase: ~, internal_app_id: 0000001}
            prod_free: { sandbox: false, pem: %kernel.root_dir%/../pushcerts/prod/certificate.pem, passphrase: ~, internal_app_id: 0000001}
            dev_prem: { sandbox: true, pem: %kernel.root_dir%/../pushcerts/dev/certificate1.pem, passphrase: ~, internal_app_id: 0000002}
            prod_prem: { sandbox: false, pem: %kernel.root_dir%/../pushcerts/prod/certificate1.pem, passphrase: ~, internal_app_id: 0000002}
    safari:
        pem: %kernel.root_dir%/../pushcerts/website/certificate.pem
        pk12: %kernel.root_dir%/../pushcerts/website/Certificates.p12
        passphrase: ~
        website_push_id: web.com.spark.spark
        icon16x16: %kernel.root_dir%/../pushcerts/website/icon_16x16.png
        icon16x16@2x: %kernel.root_dir%/../pushcerts/website/icon_16x16@2x.png
        icon32x32: %kernel.root_dir%/../pushcerts/website/icon_32x32.png
        icon32x32@2x: %kernel.root_dir%/../pushcerts/website/icon_32x32@2x.png
        icon128x128: %kernel.root_dir%/../pushcerts/website/icon_128x128.png
        icon128x128@2x: %kernel.root_dir%/../pushcerts/website/icon_128x128@2x.png
        websiteName: Spark
        allowedDomains: ['https://www.yourproject.com','https://yourproject.com']
        urlFormatString: %base_url%/%@
        webServiceURL: %base_url%
```


### Continue to the next step!
When you're done. Continue by creating the appropriate Device and Message classes:
[Step 2: Create your Device and Message classes](2-create_your_device_message_and_appevent_classes.md).
