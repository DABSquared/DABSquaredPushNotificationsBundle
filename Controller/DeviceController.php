<?php

namespace DABSquared\PushNotificationsBundle\Controller;




use DABSquared\PushNotificationsBundle\Model\AppEventInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use DABSquared\PushNotificationsBundle\Device\Types;
use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use DABSquared\PushNotificationsBundle\Model\Device;
use DABSquared\PushNotificationsBundle\Model\UserDeviceInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     *  description="Registers an iOS Device By Device Token",
     *  section="DABSquared Push Notifications (iOS)"
     * )
     *
     * @Route("device/ios/register", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_token", description="The device token returned from Apple.", strict=true)
     * @RequestParam(name="badge_allowed", requirements="[0-1]", description="Whether or not the user has allowed badges on the app.", strict=true)
     * @RequestParam(name="sound_allowed", requirements="[0-1]", description="Whether or not the user has allowed sounds on the app.", strict=true)
     * @RequestParam(name="alert_allowed", requirements="[0-1]", description="Whether or not the user has allowed alerts on the app.", strict=true)
     * @RequestParam(name="is_sandbox", requirements="[0-1]", description="Whether or not the app's certificate is using apple's sandbox.", strict=true)
     * @RequestParam(name="device_name", description="The name of the registering device.", strict=true)
     * @RequestParam(name="device_model", description="The model of the registering device.", strict=true)
     * @RequestParam(name="device_version", description="The iOS version of the registering device.", strict=true)
     * @RequestParam(name="app_name", description="The name of the app registering.", strict=false)
     * @RequestParam(name="app_version", description="The version of the app that is registering.", strict=false)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     * @RequestParam(name="device_identifier", description="The vendor device identifier of the iOS device.", strict=true)
     */
    public function registeriOSDeviceAction(ParamFetcher $paramFetcher) {
        $deviceToken = $paramFetcher->get('device_token');
        $deviceName = $paramFetcher->get('device_name');
        $deviceModel = $paramFetcher->get('device_model');
        $deviceVersion = $paramFetcher->get('device_version');
        $appName = $paramFetcher->get('app_name');
        $appVersion = $paramFetcher->get('app_version');

        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier = $paramFetcher->get('device_identifier');

        $badgeAllowed = filter_var($paramFetcher->get('badge_allowed'), FILTER_VALIDATE_BOOLEAN);
        $soundAllowed = filter_var($paramFetcher->get('sound_allowed'), FILTER_VALIDATE_BOOLEAN);
        $alertAllowed = filter_var($paramFetcher->get('alert_allowed'), FILTER_VALIDATE_BOOLEAN);
        $isSandbox = filter_var($paramFetcher->get('is_sandbox'), FILTER_VALIDATE_BOOLEAN);

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppIdAndDeviceToken(Types::OS_IOS, $deviceIdentifier, $appId,$deviceToken );

        if(is_null($device)) {
           $device = $deviceManager->createDevice($device);
        }

        if($device instanceof UserDeviceInterface) {
            $user = $this->get('security.context')->getToken()->getUser();
            $device->setUser($user);
        }
        $device->setAppId($appId);
        $device->setDeviceIdentifier($deviceIdentifier);
        $device->setDeviceModel($deviceModel);
        $device->setDeviceName($deviceName);
        $device->setDeviceVersion($deviceVersion);
        $device->setBadgeAllowed($badgeAllowed);
        $device->setSoundAllowed($soundAllowed);
        $device->setAlertAllowed($alertAllowed);
        $device->setType(Types::OS_IOS);
        $device->setAppName($appName);
        $device->setAppVersion($appVersion);
        $device->setDeviceToken($deviceToken);
        $device->setState($isSandbox ? Device::STATE_SANDBOX : Device::STATE_PRODUCTION);
        $device->setStatus(DeviceStatus::DEVICE_STATUS_ACTIVE);
        $deviceManager->saveDevice($device);

        return null;
    }


    /**
     * @ApiDoc(
     *  description="Registers an Android GCM Device By Registration ID",
     *  section="DABSquared Push Notifications (GCM)"
     * )
     *
     * @Route("device/gcm/register", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_token", description="The registration id returned from GCM", strict=true)
     * @RequestParam(name="device_identifier", description="The vendor device identifier of the Android device.", strict=true)
     * @RequestParam(name="is_sandbox", requirements="[0-1]", description="Whether or not the app's certificate is using apple's sandbox.", strict=true)
     * @RequestParam(name="device_name", description="The name of the registering device.", strict=true)
     * @RequestParam(name="device_model", description="The model of the registering device.", strict=true)
     * @RequestParam(name="device_version", description="The iOS version of the registering device.", strict=true)
     * @RequestParam(name="app_name", description="The name of the app registering.", strict=true)
     * @RequestParam(name="app_version", description="The version of the app that is registering.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function registerGCMRegistrationIDAction(ParamFetcher $paramFetcher) {
        $deviceToken = $paramFetcher->get('device_token');
        $deviceName = $paramFetcher->get('device_name');
        $deviceModel = $paramFetcher->get('device_model');
        $deviceVersion = $paramFetcher->get('device_version');
        $deviceIdentifier = $paramFetcher->get('device_identifier');
        $appName = $paramFetcher->get('app_name');
        $appVersion = $paramFetcher->get('app_version');
        $appId = $paramFetcher->get('app_id');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppIdAndDeviceToken(Types::OS_ANDROID_GCM, $deviceIdentifier, $appId, $deviceToken);

        if(is_null($device)) {
            $device = $deviceManager->createDevice($device);
        }

        if($device instanceof UserDeviceInterface) {
            $user = $this->get('security.context')->getToken()->getUser();
            $device->setUser($user);
        }

        $device->setDeviceModel($deviceModel);
        $device->setDeviceName($deviceName);
        $device->setDeviceVersion($deviceVersion);
        $device->setDeviceToken($deviceToken);
        $device->setBadgeAllowed(false);
        $device->setSoundAllowed(false);
        $device->setAlertAllowed(false);
        $device->setType(Types::OS_ANDROID_GCM);
        $device->setAppName($appName);
        $device->setAppVersion($appVersion);
        $device->setAppId($appId);
        $device->setState(Device::STATE_PRODUCTION);
        $deviceManager->saveDevice($device);

        return null;
    }



    /**
     * @ApiDoc(
     *  description="Unregisters an iOS Device",
     *  section="DABSquared Push Notifications (iOS)"
     * )
     *
     * @Route("device/ios/unregister", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_identifier", description="The vendor device identifier of the iOS device.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function unregisteriOSDeviceAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier = $paramFetcher->get('device_identifier');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_IOS, $deviceIdentifier, $appId);

        if(!is_null($device)) {
            $device->setStatus(DeviceStatus::DEVICE_STATUS_UNACTIVE);
            $device->setBadgeNumber(0);
            $deviceManager->saveDevice($device);
        }

        return null;
    }

    /**
     * @ApiDoc(
     *  description="Unregisters an Android GCM Device",
     *  section="DABSquared Push Notifications (GCM)"
     * )
     *
     * @Route("device/gcm/unregister", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_identifier", description="The vendor device identifier of the Android device.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function unregisterGCMDeviceAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier = $paramFetcher->get('device_identifier');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_ANDROID_GCM, $deviceIdentifier, $appId);

        if(!is_null($device)) {
            $device->setStatus(DeviceStatus::DEVICE_STATUS_UNACTIVE);
            $device->setBadgeNumber(0);
            $deviceManager->saveDevice($device);
        }

        return null;
    }

    /**
     * @ApiDoc(
     *  description="Registers that an iOS app opened",
     *  section="DABSquared Push Notifications (Deprecated)"
     * )
     *
     * @Route("device/app_open", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_identifier", description="The device identifier you defined.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function appOpenAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier =$paramFetcher->get('device_identifier');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $appEventManager \DABSquared\PushNotificationsBundle\Model\AppEventManagerInterface */
        $appEventManager = $this->get('dab_push_notifications.manager.appevent');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_IOS, $deviceIdentifier, $appId);

        if(!is_null($device)) {
            $device->setBadgeNumber(0);
            $deviceManager->saveDevice($device);
            $appEvent = $appEventManager->createAppEvent();
            $appEvent->setType(AppEventInterface::APP_OPEN);
            $appEvent->setDevice($device);
            $appEventManager->saveAppEvent($appEvent);
        }

        return null;
    }

    /**
     * @ApiDoc(
     *  description="Registers that an iOS app opened",
     *  section="DABSquared Push Notifications (iOS)"
     * )
     *
     * @Route("device/ios/app_open", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_identifier", description="The device identifier you defined.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function appiOSOpenAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier =$paramFetcher->get('device_identifier');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $appEventManager \DABSquared\PushNotificationsBundle\Model\AppEventManagerInterface */
        $appEventManager = $this->get('dab_push_notifications.manager.appevent');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_IOS, $deviceIdentifier, $appId);



        if(!is_null($device)) {
            $device->setBadgeNumber(0);
            $deviceManager->saveDevice($device);
            $appEvent = $appEventManager->createAppEvent();
            $appEvent->setType(AppEventInterface::APP_OPEN);
            $appEvent->setDevice($device);
            $appEventManager->saveAppEvent($appEvent);
        }

        return null;
    }

    /**
     * @ApiDoc(
     *  description="Registers that an iOS app terminated",
     *  section="DABSquared Push Notifications (iOS)"
     * )
     *
     * @Route("device/ios/app_terminated", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_identifier", description="The device identifier you defined.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function appiOSTerminatedAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier =$paramFetcher->get('device_identifier');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $appEventManager \DABSquared\PushNotificationsBundle\Model\AppEventManagerInterface */
        $appEventManager = $this->get('dab_push_notifications.manager.appevent');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_IOS, $deviceIdentifier, $appId);

        if(!is_null($device)) {
            $appEvent = $appEventManager->createAppEvent();
            $appEvent->setType(AppEventInterface::APP_TERMINATED);
            $appEvent->setDevice($device);
            $appEventManager->saveAppEvent($appEvent);
        }

        return null;
    }

    /**
     * @ApiDoc(
     *  description="Registers that an iOS app backgrounded",
     *  section="DABSquared Push Notifications (iOS)"
     * )
     *
     * @Route("device/ios/app_backgrounded", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_identifier", description="The device identifier you defined.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     */
    public function appiOSBackgroundedAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier =$paramFetcher->get('device_identifier');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $appEventManager \DABSquared\PushNotificationsBundle\Model\AppEventManagerInterface */
        $appEventManager = $this->get('dab_push_notifications.manager.appevent');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_IOS, $deviceIdentifier, $appId);

        if(!is_null($device)) {
            $appEvent = $appEventManager->createAppEvent();
            $appEvent->setType(AppEventInterface::APP_BACKGROUNDED);
            $appEvent->setDevice($device);
            $appEventManager->saveAppEvent($appEvent);
        }

        return null;
    }

    /**
     * @ApiDoc(
     *  description="Registers that a GCM app opened",
     *  section="DABSquared Push Notifications (GCM)"
     * )
     *
     * @Route("device/gcm/app_open", defaults={"_format": "json"})
     * @Method("POST")
     * @Rest\View()
     * @RequestParam(name="device_token", description="The device token returned from Apple.", strict=true)
     * @RequestParam(name="app_id", description="The internal app id that is registered in the Symfony 2 config.", strict=true)
     * @RequestParam(name="device_token", description="The registration id returned from GCM", strict=true)
     */
    public function appGCMOpenAction(ParamFetcher $paramFetcher) {
        $appId = $paramFetcher->get('app_id');
        $deviceIdentifier =$paramFetcher->get('device_identifier');
        $deviceToken = $paramFetcher->get('device_token');

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManagerInterface */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppIdAndDeviceToken(Types::OS_ANDROID_GCM, $deviceIdentifier, $appId, $deviceToken);

        if(!is_null($device)) {
            $device->setBadgeNumber(0);
            $deviceManager->saveDevice($device);
        }

        return null;
    }
}
