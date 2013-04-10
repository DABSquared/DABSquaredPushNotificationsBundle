<?php

namespace DABSquared\PushNotificationsBundle\Controller;

use DABSquared\PushNotificationsBundle\Model\UserDeviceInterface;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use DABSquared\PushNotificationsBundle\Device\Types;
use DABSquared\PushNotificationsBundle\Model\Device;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Created by JetBrains PhpStorm.
 * User: daniel_brooks
 * Date: 2/1/13
 * Time: 3:35 PM
 * To change this template use File | Settings | File Templates.
 */
class DeviceController extends Controller
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Registers an iOS Device",
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when their is an error"},
     *  filters={
     *      {"name"="device_identifier", "dataType"="string", "required"="true"},
     *      {"name"="device_token", "dataType"="boolean", "required"="true"},
     *      {"name"="badge_allowed", "dataType"="boolean", "required"="true"},
     *      {"name"="sound_allowed", "dataType"="boolean", "required"="true"},
     *      {"name"="alert_allowed", "dataType"="boolean", "required"="true"},
     *      {"name"="is_sandbox", "dataType"="boolean", "required"="true"},
     *      {"name"="device_name", "dataType"="string", "required"="true"},
     *      {"name"="device_model", "dataType"="string", "required"="true"},
     *      {"name"="device_version", "dataType"="string", "required"="true"},
     *  }
     * )
     *
     * @Route("device/ios/register", defaults={"_format": "json"})
     * @Method("POST")
     *
     */
    public function registeriOSDeviceAction()
    {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->get('request');

        $deviceIdentifier = $request->request->get('device_identifier');
        $deviceToken = $request->request->get('device_token');
        $deviceName = $request->request->get('device_name');
        $deviceModel = $request->request->get('device_model');
        $deviceVersion = $request->request->get('device_version');

        $badgeAllowed = filter_var($request->request->get('badge_allowed'), FILTER_VALIDATE_BOOLEAN);
        $soundAllowed = filter_var($request->request->get('sound_allowed'), FILTER_VALIDATE_BOOLEAN);
        $alertAllowed = filter_var($request->request->get('alert_allowed'), FILTER_VALIDATE_BOOLEAN);
        $isSandbox = filter_var($request->request->get('is_sandbox'), FILTER_VALIDATE_BOOLEAN);

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManager */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByIdentifierAndTypeAndToken($deviceIdentifier, Types::OS_IOS, $deviceToken);

        if(is_null($device)) {
           $device = $deviceManager->createDevice($device);
           $device->setDeviceIdentifier($deviceIdentifier);
           $device->setDeviceToken($deviceToken);
        }

        if($device instanceof UserDeviceInterface) {
            $user = $this->get('security.context')->getToken()->getUser();
            $device->setUser($user);
        }

        $device->setDeviceModel($deviceModel);
        $device->setDeviceName($deviceName);
        $device->setDeviceVersion($deviceVersion);
        $device->setBadgeAllowed($badgeAllowed);
        $device->setSoundAllowed($soundAllowed);
        $device->setAlertAllowed($alertAllowed);
        $device->setType(Types::OS_IOS);

        $device->setState($isSandbox ? Device::STATE_SANDBOX : Device::STATE_PRODUCTION);

        $deviceManager->saveDevice($device);

        return $this->showSuccessData($device, null);
    }





    /********************************* Helper Methods ***************************************/

    public function showErrorMessage($userMessage)
    {

        $response = array();
        $response['status'] = 401;
        $response['userMessage'] = $userMessage;
        $response['message'] = "Error";

        $view = View::create()
            ->setData($response);

        return $this->get('fos_rest.view_handler')->handle($view);
    }


    public function showSuccessData($data, $userMessage)
    {

        $response = array();
        $response['status'] = 200;
        $response['userMessage'] = $userMessage;
        $response['message'] = "Success";
        $response['data'] = $data;

        $view = View::create()
            ->setData($response);


        return $this->get('fos_rest.view_handler')->handle($view);
    }

}
