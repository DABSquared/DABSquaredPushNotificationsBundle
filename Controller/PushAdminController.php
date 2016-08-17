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
use DABSquared\PushNotificationsBundle\Device\Types;
use DABSquared\PushNotificationsBundle\Model\AppEventInterface;
use DABSquared\PushNotificationsBundle\Model\DeviceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
use Symfony\Component\Validator\Constraints\Date;


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
     * @var \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface
     * @DI\Inject("dab_push_notifications.manager.device")
     */
    private $deviceManager;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\MessageManagerInterface
     * @DI\Inject("dab_push_notifications.manager.message")
     */
    private $messageManager;

    /**
     * @var \DABSquared\PushNotificationsBundle\Model\AppEventManagerInterface
     * @DI\Inject("dab_push_notifications.manager.appevent")
     */
    private $appEventManager;

    /**
     * @var \DABSquared\PushNotificationsBundle\Service\Notifications
     * @DI\Inject("dab_push_notifications")
     */
    private $notificationManager;

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
    public function dashboardAction()
    {
        $this->session->set('dab_push_selected_nav', 'dashboard');

        $apps = $this->notificationManager->getApps();

        return array("apps" => $apps);
    }

    /**
     * @Route("push/devices", name="dabsquared_push_notifications_devices")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Devices:devices.html.twig")
     */
    public function devicesAction()
    {
        $this->session->set('dab_push_selected_nav', 'devices');
        $allDevicesQuery = $this->deviceManager->findAllDevicesQuery();
        $pagination = $this->paginator->paginate(
            $allDevicesQuery,
            $this->get('request_stack')->getCurrentRequest()->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        return array('pagination' => $pagination);
    }

    /**
     * @Route("push/device/{id}", name="dabsquared_push_notifications_device")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Devices:device.html.twig")
     */
    public function deviceAction($id)
    {
        $this->session->set('dab_push_selected_nav', 'devices');
        $device = $this->deviceManager->findDeviceWithId($id);
        $allMessagesQuery = $this->messageManager->findAllQueryByDeviceId($id);

        $pagination = $this->paginator->paginate(
            $allMessagesQuery,
            $this->get('request_stack')->getCurrentRequest()->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );
        return array('pagination' => $pagination, 'device' => $device);
    }


    /**
     * @Route("push/lookmessages", name="dabsquared_push_notifications_messages")
     * @Method({"GET"})
     * @Template("DABSquaredPushNotificationsBundle:Messages:messages.html.twig")
     */
    public function messagesAction()
    {
        $this->session->set('dab_push_selected_nav', 'messages');
        $allMessagesQuery = $this->messageManager->findAllQuery();
        $pagination = $this->paginator->paginate(
            $allMessagesQuery,
            $this->get('request_stack')->getCurrentRequest()->query->get('page', 1)/*page number*/,
            20/*limit per page*/
        );
        return array('pagination' => $pagination);
    }


    /**
     * @Route("push/create_device_message/{deviceId}", name="dabsquared_push_notifications_create_device_push_message")
     * @Method({"GET","POST"})
     * @Template("DABSquaredPushNotificationsBundle:Messages:create_message_only.html.twig")
     */
    public function createDevicePushMessageAction($deviceId)
    {
        $this->session->set('dab_push_selected_nav', 'devices');

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Request::createFromGlobals();

        $form = $this->createForm(new \DABSquared\PushNotificationsBundle\Form\MessageOnlyType());

        if (is_null($deviceId)) {
            throw new NotFoundHttpException("You need to add a device id.");
        }

        $device = $this->deviceManager->findDeviceWithId($deviceId);

        if (is_null($device)) {
            throw new NotFoundHttpException("The device with that id was not found.");
        }

        $jsonError = null;

        $payloadSent = null;

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            $messageText = $form['message']->getData();
            $messageTitle = $form['title']->getData();
            $messageSound = $form['sound']->getData();
            $messageBadge = $form['badge']->getData();
            $messageCustomData = $form['customData']->getData();

            /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
            $device = $this->deviceManager->findDeviceWithId($device);

            $message = null;
            /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
            $message =  $this->messageManager->createMessage($message);
            $message->setMessage($messageText);
            $message->setTitle($messageTitle);
            $message->setSound($messageSound);
            $message->setBadge($messageBadge);

            if (!is_null($messageCustomData)) {
                $messageCustomData = trim($messageCustomData);
                $data = json_decode($messageCustomData, true);
                $message->setCustomData($data);

                switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        break;
                    case JSON_ERROR_DEPTH:
                        $jsonError = ' - Maximum stack depth exceeded';
                        break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $jsonError = ' - Underflow or the modes mismatch';
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $jsonError = ' - Unexpected control character found';
                        break;
                    case JSON_ERROR_SYNTAX:
                        $jsonError = ' - Syntax error, malformed JSON';
                        break;
                    case JSON_ERROR_UTF8:
                        $jsonError = ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                        break;
                    default:
                        $jsonError = ' - Unknown error';
                        break;
                }
            }

            $message->setDevice($device);
            $this->messageManager->saveMessage($message);
            $payloadSent = $message->getMessageBody();
        }

        return array('form' => $form->createView(), 'device' => $device, 'json_error' => $jsonError, "payload_sent" => $payloadSent);
    }


    /**
     * @Route("push/create_message", name="dabsquared_push_notifications_create_push_messages")
     * @Method({"GET","POST"})
     * @Template("DABSquaredPushNotificationsBundle:Messages:create_message.html.twig")
     */
    public function pushMessagesAction()
    {
        $this->session->set('dab_push_selected_nav', 'create_message');

        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = Request::createFromGlobals();

        $form = $this->createForm(new \DABSquared\PushNotificationsBundle\Form\MessageType());

        if ($request->isMethod("POST")) {
            $form->bind($request);

            $types = $form['type']->getData();
            $messageText = $form['message']->getData();

            if (!empty($types)) {
                foreach ($types as $type) {
                    $devices = $this->deviceManager->findDevicesWithTypeAndStatus($type, DeviceStatus::DEVICE_STATUS_ACTIVE);
                    foreach ($devices as $device) {
                        $message = null;
                        /** @var $message \DABSquared\PushNotificationsBundle\Model\Message */
                        $message =  $this->messageManager->createMessage();
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



    /**
     * @ApiDoc(
     *  description="Gets the data to be converted for the graph",
     *  section="DABSquared Push Notifications (Dashboard Data)"
     * )
     * @Rest\View()
     * @Route("push/data/app_open_graph", defaults={"_format": "json"}, name="dabsquared_push_notifications_data_app_open_graph")
     * @Method("POST")
     * @RequestParam(name="device_state", description="What device state to grab", strict=true)
     * @RequestParam(name="internal_app_ids", description="The internal app ids to see information for.", strict=true, map=true)
     * @RequestParam(name="device_types", description="The device types to show data for", strict=true, map=true)
     * @RequestParam(name="start_date", description="Start date", strict=false)
     * @RequestParam(name="end_date", description="End date", strict=false)
     */
    public function getAppOpenGraphDataAction(ParamFetcher $paramFetcher)
    {
        $deviceState = $paramFetcher->get('device_state');
        $internalAppIds = $paramFetcher->get('internal_app_ids');
        $deviceTypes = $paramFetcher->get('device_types');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        if (is_null($startDate)) {
            $date = new \DateTime('NOW');
            $date->modify('-1 week');
            $startDate = $date;
        } else {
            $startDate = new \DateTime($startDate);
        }

        if (is_null($endDate)) {
            $endDate = new \DateTime('NOW');
        } else {
            $endDate = new \DateTime($endDate);
        }


        $appEvents = $this->appEventManager->getAppEvents(array(AppEventInterface::APP_OPEN), $deviceTypes, $internalAppIds, $deviceState, $startDate, $endDate);
        $appEventDayCounts = array();

        /** @var $appEvent \DABSquared\PushNotificationsBundle\Model\AppEventInterface */
        foreach ($appEvents as $appEvent) {
            $day = $appEvent->getCreatedAt()->format('Y-m-d');
            $appId = $appEvent->getDevice()->getAppId();

            if (!isset($appEventDayCounts[$day])) {
                $appEventDayCounts[$day] = array();
            }

            if (!isset($appEventDayCounts[$day][$appId])) {
                $appEventDayCounts[$day][$appId] = 0;
            }

            $appEventDayCounts[$day][$appId] = $appEventDayCounts[$day][$appId] + 1;
        }

        ksort($appEventDayCounts);

        $appEventCounts = array();

        foreach ($appEventDayCounts as $key => $val) {
            $tempPointArray = array();
            $tempPointArray['day'] = $key;

            foreach ($appEventDayCounts[$key] as $key1 => $val1) {
                $tempPointArray[$key1] = $val1;
            }

            $appEventCounts[] = $tempPointArray;
        }

        return $appEventCounts;
    }

    /**
     * @ApiDoc(
     *  description="Gets the data to be converted for the graph",
     *  section="DABSquared Push Notifications (Dashboard Data)"
     * )
     * @Rest\View()
     * @Route("push/data/app_terminated_graph", defaults={"_format": "json"}, name="dabsquared_push_notifications_data_app_terminated_graph")
     * @Method("POST")
     * @RequestParam(name="device_state", description="What device state to grab", strict=true)
     * @RequestParam(name="internal_app_ids", description="The internal app ids to see information for.", strict=true, map=true)
     * @RequestParam(name="device_types", description="The device types to show data for", strict=true, map=true)
     * @RequestParam(name="start_date", description="Start date", strict=false)
     * @RequestParam(name="end_date", description="End date", strict=false)
     */
    public function getAppTerminatedGraphDataAction(ParamFetcher $paramFetcher)
    {
        $deviceState = $paramFetcher->get('device_state');
        $internalAppIds = $paramFetcher->get('internal_app_ids');
        $deviceTypes = $paramFetcher->get('device_types');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        if (is_null($startDate)) {
            $date = new \DateTime('NOW');
            $date->modify('-1 week');
            $startDate = $date;
        } else {
            $startDate = new \DateTime($startDate);
        }

        if (is_null($endDate)) {
            $endDate = new \DateTime('NOW');
        } else {
            $endDate = new \DateTime($endDate);
        }


        $appEvents = $this->appEventManager->getAppEvents(array(AppEventInterface::APP_TERMINATED), $deviceTypes, $internalAppIds, $deviceState, $startDate, $endDate);
        $appEventDayCounts = array();

        /** @var $appEvent \DABSquared\PushNotificationsBundle\Model\AppEventInterface */
        foreach ($appEvents as $appEvent) {
            $day = $appEvent->getCreatedAt()->format('Y-m-d');
            $appId = $appEvent->getDevice()->getAppId();

            if (!isset($appEventDayCounts[$day])) {
                $appEventDayCounts[$day] = array();
            }

            if (!isset($appEventDayCounts[$day][$appId])) {
                $appEventDayCounts[$day][$appId] = 0;
            }

            $appEventDayCounts[$day][$appId] = $appEventDayCounts[$day][$appId] + 1;
        }

        ksort($appEventDayCounts);

        $appEventCounts = array();

        foreach ($appEventDayCounts as $key => $val) {
            $tempPointArray = array();
            $tempPointArray['day'] = $key;

            foreach ($appEventDayCounts[$key] as $key1 => $val1) {
                $tempPointArray[$key1] = $val1;
            }

            $appEventCounts[] = $tempPointArray;
        }

        return $appEventCounts;
    }

    /**
     * @ApiDoc(
     *  description="Gets the data to be converted for the graph",
     *  section="DABSquared Push Notifications (Dashboard Data)"
     * )
     * @Rest\View()
     * @Route("push/data/app_backgrounded_graph", defaults={"_format": "json"}, name="dabsquared_push_notifications_data_app_backgrounded_graph")
     * @Method("POST")
     * @RequestParam(name="device_state", description="What device state to grab", strict=true)
     * @RequestParam(name="internal_app_ids", description="The internal app ids to see information for.", strict=true, map=true)
     * @RequestParam(name="device_types", description="The device types to show data for", strict=true, map=true)
     * @RequestParam(name="start_date", description="Start date", strict=false)
     * @RequestParam(name="end_date", description="End date", strict=false)
     */
    public function getAppBackgroundedGraphDataAction(ParamFetcher $paramFetcher)
    {
        $deviceState = $paramFetcher->get('device_state');
        $internalAppIds = $paramFetcher->get('internal_app_ids');
        $deviceTypes = $paramFetcher->get('device_types');
        $startDate = $paramFetcher->get('start_date');
        $endDate = $paramFetcher->get('end_date');

        if (is_null($startDate)) {
            $date = new \DateTime('NOW');
            $date->modify('-1 week');
            $startDate = $date;
        } else {
            $startDate = new \DateTime($startDate);
        }

        if (is_null($endDate)) {
            $endDate = new \DateTime('NOW');
        } else {
            $endDate = new \DateTime($endDate);
        }


        $appEvents = $this->appEventManager->getAppEvents(array(AppEventInterface::APP_BACKGROUNDED), $deviceTypes, $internalAppIds, $deviceState, $startDate, $endDate);
        $appEventDayCounts = array();

        /** @var $appEvent \DABSquared\PushNotificationsBundle\Model\AppEventInterface */
        foreach ($appEvents as $appEvent) {
            $day = $appEvent->getCreatedAt()->format('Y-m-d');
            $appId = $appEvent->getDevice()->getAppId();

            if (!isset($appEventDayCounts[$day])) {
                $appEventDayCounts[$day] = array();
            }

            if (!isset($appEventDayCounts[$day][$appId])) {
                $appEventDayCounts[$day][$appId] = 0;
            }

            $appEventDayCounts[$day][$appId] = $appEventDayCounts[$day][$appId] + 1;
        }

        ksort($appEventDayCounts);

        $appEventCounts = array();

        foreach ($appEventDayCounts as $key => $val) {
            $tempPointArray = array();
            $tempPointArray['day'] = $key;

            foreach ($appEventDayCounts[$key] as $key1 => $val1) {
                $tempPointArray[$key1] = $val1;
            }

            $appEventCounts[] = $tempPointArray;
        }

        return $appEventCounts;
    }
}
