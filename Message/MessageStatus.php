<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 4/14/13
 * Time: 1:33 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Message;


class MessageStatus {
    const MESSAGE_STATUS_NOT_SENT = "dab_push_notifications.status.not.sent";
    const MESSAGE_STATUS_SENT = "dab_push_notifications.status.sent";
    const MESSAGE_STATUS_SENDING = "dab_push_notifications.status.sending";
    const MESSAGE_STATUS_NO_CERT = "dab_push_notifications.status.no.cert.found";
    const MESSAGE_STATUS_STREAM_ERROR = "dab_push_notifications.status.stream.error";

}


