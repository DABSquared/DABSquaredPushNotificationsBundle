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

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    private $deviceManager;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\MessageManager
     * @DI\Inject("dab_push_notifications.manager.message")
     */
    private $messageManager;

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
        $allDevicesQuery = $this->deviceManager->findAllDevicesQuery();
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
        $device = $this->deviceManager->findDeviceWithId($id);
        $allMessagesQuery = $this->messageManager->findAllQueryByDeviceId($id);

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
     * @Route("push/create_device_message/{deviceId}", name="dabsquared_push_notifications_create_device_push_message")
     * @Method({"GET","POST"})
     * @Template("DABSquaredPushNotificationsBundle:Messages:create_message.html.twig")
     */
    public function createDevicePushMessageAction($deviceId) {
        $this->session->set('dab_push_selected_nav', 'devices');

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Request::createFromGlobals();

        $form = $this->createForm(new \DABSquared\PushNotificationsBundle\Form\MessageType($this->container->getParameter('dab_push_notifications.model.device.class')));

        if(!is_null($deviceId)) {
            $device = $this->deviceManager->findDeviceWithId($deviceId);
            if(is_null($device)) {
                throw new NotFoundHttpException("The device with that id was not found.");
            }
            $form->setData($device);
        } else {
            throw new NotFoundHttpException("The device with that id was not found.");
        }


        if($request->isMethod("POST")) {
            $form->bind($request);

            $types = $form['type']->getData();
            $messageText = $form['message']->getData();

            if(!is_null($device) && $device != "") {
                /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
                $device = $this->deviceManager->findDeviceWithId($device);

                $message = null;
                /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
                $message =  $this->messageManager->createMessage($message);
                $message->setMessage($messageText);
                $message->setDevice($device);
                $this->messageManager->saveMessage($message);

            } else if(!empty($types)) {

                foreach($types as $type) {

                    $devices = $this->deviceManager->findDevicesWithTypeAndStatus($type, DeviceStatus::DEVICE_STATUS_ACTIVE);
                    foreach($devices as $device) {
                        $message = null;
                        /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
                        $message =  $this->messageManager->createMessage($message);
                        $message->setMessage($messageText);
                        $message->setDevice($device);
                        $this->messageManager->saveMessage($message);
                    }
                }
            }
        }

        return array('form' => $form->createView());
    }


    /**
     * @Route("push/create_message", name="dabsquared_push_notifications_create_push_messages")
     * @Method({"GET","POST"})
     * @Template("DABSquaredPushNotificationsBundle:Messages:create_message.html.twig")
     */
    public function pushMessagesAction() {
        $this->session->set('dab_push_selected_nav', 'create_message');

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Request::createFromGlobals();

        $form = $this->createForm(new \DABSquared\PushNotificationsBundle\Form\MessageType($this->container->getParameter('dab_push_notifications.model.device.class')));

        if($request->isMethod("POST")) {
            $form->bind($request);

            $types = $form['type']->getData();
            $messageText = $form['message']->getData();

            if(!empty($types)) {

                foreach($types as $type) {

                   $devices = $this->deviceManager->findDevicesWithTypeAndStatus($type, DeviceStatus::DEVICE_STATUS_ACTIVE);
                   foreach($devices as $device) {
                       $message = null;
                       /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
                       $message =  $this->messageManager->createMessage($message);
                       $message->setMessage($messageText);
                       $message->setDevice($device);
                       $this->messageManager->saveMessage($message);
                   }
               }
            } else {
                throw new BadRequestHttpException("You need to specify the device types to send to.");
            }
        }

        return array('form' => $form->createView());
    }

}