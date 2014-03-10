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
            $headers[] = "Authorization: GoogleLogin auth=" . $this->authToken;
            $data = $message->getMessageBody();

            $buzz = new Browser();
            $buzz->getClient()->setVerifyPeer(false);
            $response = $buzz->post("https://android.apis.google.com/c2dm/send", $headers, http_build_query($data));
            return preg_match("/^id=/", $response->getContent()) > 0;
        }

        return false;
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

        $buzz = new Browser();
        $buzz->getClient()->setVerifyPeer(false);
        $response = $buzz->post("https://www.google.com/accounts/ClientLogin", array(), http_build_query($data));
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        preg_match("/Auth=([a-z0-9_\-]+)/i", $response->getContent(), $matches);
        $this->authToken = $matches[1];
        return true;
    }

    public function sendMessages(array $messages){
        throw new \Exception('Not implemented');
    }
}
