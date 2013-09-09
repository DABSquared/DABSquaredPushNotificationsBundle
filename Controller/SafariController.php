<?php

namespace DABSquared\PushNotificationsBundle\Controller;

use DABSquared\PushNotificationsBundle\Device\DeviceStatus;
use DABSquared\PushNotificationsBundle\Model\UserDeviceInterface;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\View\RouteRedirectView;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
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
class SafariController extends Controller
{



    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Logs an error to the monolog.",
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when their is an error"},
     *  filters={
     *  }
     * )
     *
     * @Route("v1/log", defaults={"_format": "json"})
     * @Method("POST")
     *
     */
    public function logSafariErrorAction() {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->get('request');

        /** @var $logger \Symfony\Component\HttpKernel\Log\LoggerInterface */
        $logger = $this->container->get('logger');
        $logs = json_decode($request->getContent());
        $logs = $logs->logs;

        foreach($logs as $log) {
            $logger->error("Push error: ".$log);
        }

        return new Response();
    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Register Safari Device",
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when their is an error"},
     *  filters={
     *      {"name"="websitePushID", "dataType"="string", "required"="true"},
     *      {"name"="deviceToken", "dataType"="string", "required"="true"},
     *  }
     * )
     *
     * @Route("v1/devices/{deviceToken}/registrations/{websitePushID}", defaults={"_format": "json"})
     * @Method({"POST","GET","DELETE"})
     *
     */
    public function registerSafariDeviceAction($deviceToken,$websitePushID) {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->get('request');
        $request->headers = $this->fixAuthHeader($request->headers);

        $authenticationToken = $request->headers->get("Authorization");
        $userId = str_replace("ApplePushNotifications authenticationToken_", "", $authenticationToken);

        /** @var $deviceManager \DABSquared\PushNotificationsBundle\Model\DeviceManager */
        $deviceManager = $this->get('dab_push_notifications.manager.device');

        /** @var $device \DABSquared\PushNotificationsBundle\Model\Device */
        $device = $deviceManager->findDeviceByTypeIdentifierAndAppId(Types::OS_SAFARI, $deviceToken, $websitePushID);

        /** @var $logger \Symfony\Component\HttpKernel\Log\LoggerInterface */
        $logger = $this->container->get('logger');
        if($request->getContent()) {
            $logger->notice(print_r($request->getContent(),true));
        }


        if(!is_null($device) && $request->isMethod("DELETE")) {
            $device->setStatus(DeviceStatus::DEVICE_STATUS_UNACTIVE);
        } else if(!is_null($device)) {
            $device->setBadgeNumber(0);
            $device->setStatus(DeviceStatus::DEVICE_STATUS_ACTIVE);
        }  else {
            $device = $deviceManager->createDevice($device);
            $device->setAppId($websitePushID);
            $device->setDeviceIdentifier($deviceToken);
            $device->setType(Types::OS_SAFARI);
            $device->setDeviceToken($deviceToken);
            $device->setDeviceVersion($request->headers->get("User-Agent"));
            $device->setDeviceName("Safari: ".$userId);
            $device->setStatus(DeviceStatus::DEVICE_STATUS_ACTIVE);
            if($device instanceof UserDeviceInterface) {
                $userEntityNamespace = $this->container->getParameter('dab_push_notifications.user_entity_namespace');
                if(!is_null($userEntityNamespace)) {
                    /** @var $em \Doctrine\ORM\EntityManager */
                    $em = $this->container->get('doctrine')->getManager();
                    /** @var $userRepository \Doctrine\ORM\EntityRepository */
                    $userRepository = $em->getRepository($userEntityNamespace);
                    $user = $userRepository->findOneBy(array(
                        "id" => $userId
                    ));
                    if(!is_null($user)) {
                        $device->setUser($user);
                    }
                }
            }
        }

        $deviceManager->saveDevice($device);

        return new Response();
    }


    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Download Payload For Safari Push",
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when their is an error"},
     *  filters={
     *      {"name"="app_id", "dataType"="string", "required"="true"},
     *      {"name"="device_identifier", "dataType"="string", "required"="true"},
     *  }
     * )
     *
     * @Route("v1/pushPackages/{websitePushID}", defaults={"_format": "json"})
     * @Method({"POST","GET"})
     *
     */
    public function downloadSafariDevicePayloadAction($websitePushID) {
        /** @var $request \Symfony\Component\HttpFoundation\Request */
        $request = $this->get('request');

        if($request->getContent()== null) {
            return new Response();
        }

        $userJSON = json_decode($request->getContent());

        $authenticationToken = "authenticationToken_".$userJSON->userId;

        $zipName = $this->buildPushPackage($websitePushID,$authenticationToken);

        if(is_null($zipName)) {
            return new Response();
        }

        $response = new Response();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$zipName.'"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', filesize($zipName));
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Expires', '0');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Cache-Control', 'private');
        $response->setContent(file_get_contents($zipName));

        @unlink($zipName);

        return $response;

    }


    function buildPushPackage($websitePushID, $authenticationToken) {
        $pushPackage = new \ZipArchive;
        $zipName = "web.com.curveu.zip";
        if ($pushPackage->open($zipName, \ZIPARCHIVE::CREATE | \ZIPARCHIVE::OVERWRITE) === TRUE) {

            $icon16x16 = $this->container->getParameter('dab_push_notifications.safari.icon16x16');
            $icon16x162x = $this->container->getParameter('dab_push_notifications.safari.icon16x16@2x');
            $icon32x32 = $this->container->getParameter('dab_push_notifications.safari.icon32x32');
            $icon32x322x = $this->container->getParameter('dab_push_notifications.safari.icon32x32@2x');
            $icon128x128 = $this->container->getParameter('dab_push_notifications.safari.icon128x128');
            $icon128x1282x = $this->container->getParameter('dab_push_notifications.safari.icon128x128@2x');

            $websiteName = $this->container->getParameter('dab_push_notifications.safari.websiteName');
            $allowedDomains = $this->container->getParameter('dab_push_notifications.safari.allowedDomains');
            $urlFormatString = $this->container->getParameter('dab_push_notifications.safari.urlFormatString');
            $webServiceURL = $this->container->getParameter('dab_push_notifications.safari.webServiceURL');
            $pk12Path = $this->container->getParameter('dab_push_notifications.safari.pk12');
            $pem = $this->container->getParameter('dab_push_notifications.safari.pem');
            $passphrase = $this->container->getParameter('dab_push_notifications.safari.passphrase');


            $icon16x16Hash = sha1(file_get_contents($icon16x16));
            $icon16x162xHash = sha1(file_get_contents($icon16x162x));
            $icon32x32Hash = sha1(file_get_contents($icon32x32));
            $icon32x322xHash = sha1(file_get_contents($icon32x322x));
            $icon128x128Hash = sha1(file_get_contents($icon128x128));
            $icon128x1282xHash = sha1(file_get_contents($icon128x1282x));


            $pushPackage->addEmptyDir("icon.iconset");


            $pushPackage->addFromString('icon.iconset/icon_16x16.png', file_get_contents($icon16x16));
            $pushPackage->addFromString('icon.iconset/icon_16x16@2x.png', file_get_contents($icon16x162x));
            $pushPackage->addFromString('icon.iconset/icon_32x32.png', file_get_contents($icon32x32));
            $pushPackage->addFromString('icon.iconset/icon_32x32@2x.png', file_get_contents($icon32x322x));
            $pushPackage->addFromString('icon.iconset/icon_128x128.png', file_get_contents($icon128x128));
            $pushPackage->addFromString('icon.iconset/icon_128x128@2x.png', file_get_contents($icon128x1282x));


            $websiteJSONDict = array();
            $websiteJSONDict['websiteName'] = $websiteName;
            $websiteJSONDict['websitePushID'] = $websitePushID;
            $websiteJSONDict['allowedDomains'] = $allowedDomains;
            $websiteJSONDict['urlFormatString'] = $urlFormatString;
            $websiteJSONDict['webServiceURL'] = $webServiceURL;
            $websiteJSONDict['authenticationToken'] = "authenticationToken_1234";




            $websiteDictContents = str_replace("\\/","/",$this->prettyPrint(json_encode($websiteJSONDict)));
            $websiteDictContentsHash = sha1($websiteDictContents);

            $pushPackage->addFromString('website.json', $websiteDictContents);


            $manifestJSONDict = array();
            $manifestJSONDict['website.json'] = $websiteDictContentsHash;
            $manifestJSONDict['icon.iconset/icon_16x16.png'] = $icon16x16Hash;
            $manifestJSONDict['icon.iconset/icon_16x16@2x.png'] = $icon16x162xHash;
            $manifestJSONDict['icon.iconset/icon_32x32.png'] = $icon32x32Hash;
            $manifestJSONDict['icon.iconset/icon_32x32@2x.png'] = $icon32x322xHash;
            $manifestJSONDict['icon.iconset/icon_128x128.png'] = $icon128x128Hash;
            $manifestJSONDict['icon.iconset/icon_128x128@2x.png'] = $icon128x1282xHash;

            $manifestDictContents = json_encode($manifestJSONDict);
            $pushPackage->addFromString('manifest.json', $manifestDictContents);

            $pkcs12 = file_get_contents($pk12Path);
            $certs = array();
            if(!openssl_pkcs12_read($pkcs12, $certs, $passphrase)) {
                return;
            }

            $cert_data = openssl_x509_read($certs['cert']);
            $private_key = openssl_pkey_get_private($certs['pkey'], $passphrase);

            $tempManifest = "tmp".time().".json";
            $tempManifestSigned = "tmp".time()."signed.json";

            $fp = fopen($tempManifest, "w");
            fwrite($fp, $manifestDictContents);
            fclose($fp);

            openssl_pkcs7_sign($tempManifest, $tempManifestSigned, $cert_data, $private_key, array(), PKCS7_BINARY | PKCS7_DETACHED);

            $signedManifestString = file_get_contents($tempManifestSigned);
            $matches = array();
            if (!preg_match('~Content-Disposition:[^\n]+\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----~', $signedManifestString, $matches)) {
                return;
            }
            $signature_der = base64_decode($matches[1]);
            $pushPackage->addFromString('signature', $signature_der);

            $pushPackage->close();

            @unlink($tempManifest);
            @unlink($tempManifestSigned);

            return $zipName;
        }

        return null;
    }


    /**
     * PHP does not include HTTP_AUTHORIZATION in the $_SERVER array, so this header is missing.
     * We retrieve it from apache_request_headers()
     *
     * @param HeaderBag $headers
     */
    protected function fixAuthHeader(HeaderBag $headers)
    {
        if (!$headers->has('Authorization') && function_exists('apache_request_headers')) {
            $all = apache_request_headers();
            if (isset($all['Authorization'])) {
                $headers->set('Authorization', $all['Authorization']);
            }
        }

        return $headers;
    }

    function prettyPrint( $json )
    {
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if( $char === '"' && $prev_char != '\\' ) {
                $in_quotes = !$in_quotes;
            } else if( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                    case '{': case '[':
                    $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
                }
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat( "\t", $new_line_level );
            }
            $result .= $char.$post;
            $prev_char = $char;
        }

        return $result;
    }


}
