<?php
/**
 * Created by JetBrains PhpStorm.
 * User: daniel
 * Date: 6/2/13
 * Time: 3:35 PM
 * To change this template use File | Settings | File Templates.
 */

namespace DABSquared\PushNotificationsBundle\Controller;

use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use FOS\RestBundle\View\View;

class PushAdminController extends Controller
{

    /**
     * @Route("push/dashboard", name="dabsquared_push_notifications_dashboard")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Dashboard:dashboard.html.twig")
     */
    public function dashboardAction() {
        return array();
    }



    /**
     * @Route("push/messages", name="create_push_messages")
     * @Method({"GET","POST"})
     */
    public function pushMessagesAction() {
        /*************************************
         *  Performing standard Security Checks
         *************************************/

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Request::createFromGlobals();

        /*************************************
         * End Standard Security Checks
         ************************************/

        $form = $this->createForm(new \DABSquared\PushNotificationsBundle\Form\MessageType($this->container->getParameter('dab_push_notifications.model.device.class')));

        if($request->isMethod("POST")) {
            $form->bind($request);

            $types = $form['type']->getData();
            $device = $form['device']->getData();
            $messageText = $form['message']->getData();

            /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManager */
            $deviceManager = $this->get('dab_push_notifications.manager.device');

            /** @var $messageManager \DABSquared\PushNotificationsBundle\Model\MessageManager */
            $messageManager = $this->get('dab_push_notifications.manager.message');


            if(!is_null($device) && $device != "") {
                /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
                $device =$deviceManager->findDeviceWithId($device);

                $message = null;
                /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
                $message = $messageManager->createMessage($message);
                $message->setMessage($messageText);
                $message->setDevice($device);
                $messageManager->saveMessage($message);

            } else if(!empty($types)) {

                foreach($types as $type) {

                   $devices = $deviceManager->findDevicesWithTypeAndStatus($type, DeviceStatus::DEVICE_STATUS_ACTIVE);
                   foreach($devices as $device) {
                       $message = null;
                       /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
                       $message = $messageManager->createMessage($message);
                       $message->setMessage($messageText);
                       $message->setDevice($device);
                       $messageManager->saveMessage($message);
                   }
               }
            }
        }

        return $this->render('DABSquaredPushNotificationsBundle::messagetype.html.twig', array('form' => $form->createView()));
    }


    /**
     * @Route("push/devices/list", name="get_device_list")
     * @Method({"GET"})
     */
    public function getDeviceListAction() {

        $deviceName = $this->get('request')->query->get('term');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManager */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        $devices = $deviceManager->findDeviceWithName($deviceName);
        if (empty($devices)) {
            $devices = array();
            $aDevice['deviceName'] = 'None';
            $aDevice['id'] = '-1';
            $devices[] = $aDevice;
            return new Response(json_encode($devices));
        }

        $allDevices = array();

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        foreach($devices as $device) {
            $aDevice = array();

            $string = $device->getDeviceName().' - '.$device->getAppName();

            $aDevice['deviceName'] = $string;
            $aDevice['id'] = $device->getId();
            $allDevices[] = $aDevice;
        }


        return new Response(json_encode($allDevices));
    }

}