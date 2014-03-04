<?php

namespace DABSquared\PushNotificationsBundle\Service\OS;

use DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    DABSquared\PushNotificationsBundle\Model\Message,
    DABSquared\PushNotificationsBundle\Model\MessageInterface,
    DABSquared\PushNotificationsBundle\Device\Types;
use Buzz\Browser;


class AndroidNotification implements OSNotificationServiceInterface
{
    /**
     * Username for auth
     *
     * @var string
     */
    protected $username;

    /**
     * Password for auth
     *
     * @var string
     */
    protected $password;

    /**
     * The source of the notification
     * eg com.example.myapp
     *
     * @var string
     */
    protected $source;

    /**
     * Authentication token
     *
     * @var string
     */
    protected $authToken;

    /**
     * Constructor
     *
     * @param $username
     * @param $password
     * @param $source
     */
    public function __construct($username, $password, $source)
    {
        $this->username = $username;
        $this->password = $password;
        $this->source = $source;
        $this->authToken = "";
    }

    /**
     * Sends a C2DM message
     * This assumes that a valid auth token can be obtained
     *
     * @param \DABSquared\PushNotificationsBundle\Model\MessageInterface $message
     * @throws \DABSquared\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if ($message->getTargetOS() != Types::OS_ANDROID_C2DM) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by C2DM", get_class($message)));
        }

        if ($this->getAuthToken()) {
            $data = $message->getMessageBody();

            $headers = array(
                "Content-Type: application/json",
                "Authorization: GoogleLogin auth=" . $this->authToken
            );

            $ch = curl_init();
            // Set the url, number of POST vars, POST data
            curl_setopt( $ch, CURLOPT_URL, "https://android.apis.google.com/c2dm/send");

            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );

            // Execute post
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);

            if (empty($info['http_code']) || $info['http_code'] != 200) {
                return false;
            }
            // Close connection
            curl_close($ch);

            return preg_match("/^id=/", $response) > 0;
        }

        return false;
    }


    public function sendMessages(array $messages){
        foreach($messages as $message) {
           $this->send($message);
        }
     }

    /**
     * Gets a valid authentication token
     *
     * @return bool
     */
    protected function getAuthToken()
    {
        $data = array(
            "Email"         => $this->username,
            "Passwd"        => $this->password,
            "accountType"   => "HOSTED_OR_GOOGLE",
            "source"        => $this->source,
            "service"       => "ac2dm"
        );

        $headers = array(
            "Content-Type: application/json",
        );

        $ch = curl_init();
        // Set the url, number of POST vars, POST data
        curl_setopt( $ch, CURLOPT_URL, "https://www.google.com/accounts/ClientLogin");

        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );

        // Execute post
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        if (empty($info['http_code']) || $info['http_code'] != 200) {
            return false;
        }

        // Close connection
        curl_close($ch);

        preg_match("/Auth=([a-z0-9_\-]+)/i", $response, $matches);
        $this->authToken = $matches[1];
        return true;
    }
}
