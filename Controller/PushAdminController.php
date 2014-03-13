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

use JMS\DiExtraBundle\Annotation as DI;


class PushAdminController extends Controller
{

    /**
     * @var \Doctrine\ORM\EntityManager
     * @DI\Inject("doctrine.orm.entity_manager")
     */
    private $em;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session
     * @DI\Inject("session")
     */
    private $session;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceManager
     * @DI\Inject("dab_push_notifications.manager.device")
     */
    private $deviceManger;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\MessageManager
     * @DI\Inject("dab_push_notifications.manager.message")
     */
    private $messageManger;

    /**
     * @var \Knp\Component\Pager\Paginator
     * @DI\Inject("knp_paginator")
     */
    private $paginator;

    /**
     * @Route("push/dashboard", name="dabsquared_push_notifications_dashboard")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Dashboard:dashboard.html.twig")
     */
    public function dashboardAction() {
        $this->session->set('dab_push_selected_nav', 'dashboard');

        return array();
    }

    /**
     * @Route("push/devices", name="dabsquared_push_notifications_devices")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Devices:devices.html.twig")
     */
    public function devicesAction() {
        $this->session->set('dab_push_selected_nav', 'devices');
        $allDevicesQuery = $this->deviceManger->findAllDevicesQuery();
        $pagination = $this->paginator->paginate(
            $allDevicesQuery,
            $this->get('request')->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        return array('pagination' => $pagination);
    }

    /**
     * @Route("push/device/{id}", name="dabsquared_push_notifications_device")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Devices:device.html.twig")
     */
    public function deviceAction($id) {
        $this->session->set('dab_push_selected_nav', 'devices');
        $device = $this->deviceManger->findDeviceWithId($id);
        $allMessagesQuery = $this->messageManger->findAllQueryByDeviceId($id);

        $pagination = $this->paginator->paginate(
            $allMessagesQuery,
            $this->get('request')->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );
        return array('pagination' => $pagination, 'device' => $device);
    }


    /**
     * @Route("push/lookmessages", name="dabsquared_push_notifications_messages")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Messages:messages.html.twig")
     */
    public function messagesAction() {
        $this->session->set('dab_push_selected_nav', 'messages');
        return array();
    }



    /**
     * @Route("push/messages", name="create_push_messages")
     * @Method({"GET","POST"})
     */
    public function pushMessagesAction() {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Request::createFromGlobals();

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