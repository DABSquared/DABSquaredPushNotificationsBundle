<?php

namespace DABSquared\PushNotificationsBundle\Controller;

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
     * This the documentation description of your method, it will appear
     * on a specific pane. It will read all the text until the first
     * annotation.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of your API method",
     * )
     *
     * @Route("device/ios/register", defaults={"_format": "json"})
     * @Method("POST")
     *
     */
    public function registerDeviceAction()
    {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->get('request');

        $deviceIdentifier = $request->request->get('device_identifier');
        $deviceToken = $request->request->get('device_token');
        $badgeAllowed = filter_var($request->request->get('badge_allowed'), FILTER_VALIDATE_BOOLEAN);
        $soundAllowed = filter_var($request->request->get('sound_allowed'), FILTER_VALIDATE_BOOLEAN);
        $alertAllowed = filter_var($request->request->get('alert_allowed'), FILTER_VALIDATE_BOOLEAN);

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManager */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByIdentifierAndType($deviceIdentifier, Types::OS_IOS, $deviceToken);

        if(is_null($device)) {
           $device = $deviceManager->createDevice($device);
           $device->setDeviceIdentifier($deviceIdentifier);
           $device->setDeviceToken($deviceToken);
        }

        $device->setBadgeAllowed($badgeAllowed);
        $device->setSoundAllowed($soundAllowed);
        $device->setAlertAllowed($alertAllowed);

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
