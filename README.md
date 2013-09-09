# DABSquaredPushNotificationsBundle ![](https://secure.travis-ci.org/bassrock499/DABSquaredPushNotificationsBundle.png)

A bundle to allow sending of push notifications to mobile devices.  Currently supports Android (C2DM, GCM), Blackberry, Safari and iOS devices. The Base framework is imported from https://github.com/richsage/RMSPushNotificationsBundle

Almost Ready for Use.. Any contributions are welcome. The goal here is to provide an interface for push notifications with device registration and user device pairing just like FOSCommentBundle.

WORKS WITH SAFARI PUSH NOTIFICATIONS!!!

Road Map:

V1.0:
  Support Basic Device Registration for iOS Devices (at least)   - WORKING
  Be able to associate a device with a user much like FOSCommentBundle   - WORKING
  Be able to send messages to a particular device or user, using about 2-4 lines of code.  - WORKING
  Auto-manage badge numbers.   WORKING, NEEED TO ADD DOCUMENTATION FOR HOW TO SUPPORT.

V1.1:
  Safari Push Notifications - WORKING

V2.0:
  All other device push notifications working.
  Push Notification read receipts and statistics like UrbanAirship.


Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle:

[Read the Documentation](https://github.com/DABSquared/DABSquaredPushNotificationsBundle/blob/master/Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](https://github.com/DABSquared/DABSquaredPushNotificationsBundle/blob/master/Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE


Configuration
-------

Below you'll find all configuration options; just use what you need:

``` yaml
    dab_push_notifications:
      android:
          c2dm:
              username: <string_android_c2dm_username>
              password: <string_android_c2dm_password>
              source: <string_android_c2dm_source>
          gcm:
              api_key: <string_android_gcm_api_key>
      ios:
          certificates:  #replace these certs with your own as well as app ids. The bundle will loop through all certs displayed here when sending a push based on the sandbox param. You can add as many certificates as you need. Also note that the bundle will send using certificates that match the internal_app_ids of the registered devices.
            dev_prem: { sandbox: true, pem: %kernel.root_dir%/../pushcerts/premium/dev/certificate.pem, passphrase: ~, internal_app_id: 0000001}
            dev_lite: { sandbox: true, pem: %kernel.root_dir%/../pushcerts/lite/dev/certificate.pem, passphrase: ~, internal_app_id: 0000002}
            prod_prem: { sandbox: false, pem: %kernel.root_dir%/../pushcerts/premium/prod/certificate.pem, passphrase: ~, internal_app_id: 0000001}
            prod_lite: { sandbox: false, pem: %kernel.root_dir%/../pushcerts/lite/prod/certificate.pem, passphrase: ~,internal_app_id: 0000002}
      blackberry:
          evaluation: <bool_bb_evaluation_mode>
          app_id: <string_bb_app_id>
          password: <string_bb_password>
      safari:
          pem: %kernel.root_dir%/../pushcerts/safari/safari_push.pem
          pk12: %kernel.root_dir%/../pushcerts/safari/Certificates.p12
          passphrase: ~
          website_push_id: web.com.demo
          icon16x16: %kernel.root_dir%/../pushcerts/safari/icon_16x16.png
          icon16x16@2x: %kernel.root_dir%/../pushcerts/safari/icon_16x16@2x.png
          icon32x32: %kernel.root_dir%/../pushcerts/safari/icon_32x32.png
          icon32x32@2x: %kernel.root_dir%/../pushcerts/safari/icon_32x32@2x.png
          icon128x128: %kernel.root_dir%/../pushcerts/safari/icon_128x128.png
          icon128x128@2x: %kernel.root_dir%/../pushcerts/safari/icon_128x128@2x.png
          websiteName: Demo Site
          allowedDomains: ["https://demo.com","https://www.demo.com"]
          urlFormatString: http://www.demo.com/%@
          webServiceURL: https://www.demo.com
```



## DABSquared New Usage

Send to a User:

``` php
use DABSquared\PushNotificationsBundle\Message\iOSMessage;

class PushDemoController extends Controller
{
    public function pushAction($aUser)
    {

        foreach($aUser->getDevices() as $device) {

            $message = new Message();
            $message->setMessage('Oh my! A push notification!');
            $message->setDevice($device);
            $this->container->get('dab_push_notifications')->send($message);

        }

        return new Response('Push notification send!');
    }
}
```

Send to a Device:

``` php
use DABSquared\PushNotificationsBundle\Message\iOSMessage;

class PushDemoController extends Controller
{
    public function pushAction($aDevice)
    {
        $message = new Message();
        $message->setMessage('Oh my! A push notification!');
        $message->setDevice($aDevice);

        $this->container->get('dab_push_notifications')->send($message);

        return new Response('Push notification send!');
    }
}
```

