# DABSquaredPushNotificationsBundle ![](https://secure.travis-ci.org/bassrock499/DABSquaredPushNotificationsBundle.png)

A bundle to allow sending of push notifications to mobile devices.  Currently supports Android (C2DM, GCM), Blackberry and iOS devices. The Base framework is imported from https://github.com/richsage/RMSPushNotificationsBundle

Almost Ready for Use.. Any contributions are welcome. The goal here is to provide an interface for push notifications with device registration and user device pairing just like FOSCommentBundle.


Road Map:

V1.0:
  Support Basic Device Registration for iOS Devices (at least)
  Be able to associate a device with a user much like FOSCommentBundle
  Be able to send messages to a particular device or user, using about 2-4 lines of code.

V1.1:
  Full Support for all types of Device Push Notifications

V2.0:
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
          certificates:  #replace these certs with your own. The bundle will loop through all certs displayed here when sending a push based on the sandbox param. You can add as many certificates as you need.
              dev_prem: { sandbox: true, pem: %kernel.root_dir%/../pushcerts/premium/dev/certificate.pem, passphrase: ~}
              dev_lite: { sandbox: true, pem: %kernel.root_dir%/../pushcerts/lite/dev/certificate.pem, passphrase: ~}
              prod_prem: { sandbox: false, pem: %kernel.root_dir%/../pushcerts/premium/prod/certificate.pem, passphrase: ~}
              prod_lite: { sandbox: false, pem: %kernel.root_dir%/../pushcerts/lite/prod/certificate.pem, passphrase: ~}
      blackberry:
          evaluation: <bool_bb_evaluation_mode>
          app_id: <string_bb_app_id>
          password: <string_bb_password>
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

