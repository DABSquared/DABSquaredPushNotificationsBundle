# DABSquaredPushNotificationsBundle ![](https://secure.travis-ci.org/bassrock499/DABSquaredPushNotificationsBundle.png)

A bundle to allow sending of push notifications to mobile devices.  Currently supports Android (C2DM, GCM), Blackberry and iOS devices.

## Installation

Not Yet Ready for Use.. Any contributions are welcome. The goal here is to provide an interface for push notifications with device registration and user device pairing just like FOSCommentBundle.

The Base framework is imported from https://github.com/richsage/RMSPushNotificationsBundle


Road Map:

V1.0:
  Support Basic Device Registration for iOS Devices (at least)
  Be able to associate a device with a user much like FOSCommentBundle
  Be able to send messages to a particular device or user, using about 2-4 lines of code.

V1.1:
  Full Support for all types of Device Push Notifications

V2.0:
  Push Notification read receipts and statistics like UrbanAirship.



## Configuration

Below you'll find all configuration options; just use what you need:

    dab_push_notifications:
      android:
          c2dm:
              username: <string_android_c2dm_username>
              password: <string_android_c2dm_password>
              source: <string_android_c2dm_source>
          gcm:
              api_key: <string_android_gcm_api_key>
      ios:
          sandbox: <bool_use_apns_sandbox>
          pem: <path_apns_certificate>
          passphrase: <string_apns_certificate_passphrase>
      blackberry:
          evaluation: <bool_bb_evaluation_mode>
          app_id: <string_bb_app_id>
          password: <string_bb_password>




## DABSquared New Usage

Send to a User:

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

Send to a Device:

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


## RMS Imported Usage

A little example of how to push your first message to an iOS device, we'll assume that you've set up the configuration correctly:

    use DABSquared\PushNotificationsBundle\Message\iOSMessage;

    class PushDemoController extends Controller
    {
        public function pushAction()
        {
            $message = new iOSMessage();
            $message->setMessage('Oh my! A push notification!');
            $message->setDeviceIdentifier('test012fasdf482asdfd63f6d7bc6d4293aedd5fb448fe505eb4asdfef8595a7');

            $this->container->get('dab_push_notifications')->send($message);

            return new Response('Push notification send!');
        }
    }

The send method will detect the type of message so if you'll pass it an `AndroidMessage` it will automatically send it through the C2DM/GCM servers, and likewise for Blackberry.

## Android messages

Since both C2DM and GCM are still available, the `AndroidMessage` class has a small flag on it to toggle which service to send it to.  Use as follows:

    use DABSquared\PushNotificationsBundle\Message\AndroidMessage;

    $message = new AndroidMessage();
    $message->setGCM(true);
    
to send as a GCM message rather than C2DM.

