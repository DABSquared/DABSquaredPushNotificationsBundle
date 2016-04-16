<?php

namespace DABSquared\PushNotificationsBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DABSquaredPushNotificationsExtension extends Extension
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Loads any resources/services we need
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \InvalidArgumentException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $this->container = $container;

        $config = $processor->process($configuration->getConfigTreeBuilder(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));


        if (!in_array(strtolower($config['db_driver']), array('mongodb', 'orm'))) {
            throw new \InvalidArgumentException(sprintf('Invalid db driver "%s".', $config['db_driver']));
        }

        $loader->load(sprintf('%s.xml', $config['db_driver']));
        $loader->load('services.xml');


        $container->setParameter('dab_push_notifications.model.device.class', $config['class']['model']['device']);
        $container->setParameter('dab_push_notifications.model.message.class', $config['class']['model']['message']);
        $container->setParameter('dab_push_notifications.model.appevent.class', $config['class']['model']['appevent']);

        $container->setParameter('dab_push_notifications.model_manager_name', $config['model_manager_name']);
        $container->setParameter('dab_push_notifications.user_entity_namespace', $config['user_entity_namespace']);

        $container->setAlias('dab_push_notifications.manager.device', $config['service']['manager']['device']);
        $container->setAlias('dab_push_notifications.manager.message', $config['service']['manager']['message']);
        $container->setAlias('dab_push_notifications.manager.appevent', $config['service']['manager']['appevent']);

        $this->setInitialParams();


        if ($config['use_bcc_resque']) {
            $bundles = $container->getParameter('kernel.bundles');

            $foundBCCResque = false;

            foreach ($bundles as $name => $class) {
                $ref = new \ReflectionClass($class);
                if($ref->getName() == "BCC\\ResqueBundle\\BCCResqueBundle") {
                    $foundBCCResque = true;
                    break;
                }
            }

            if(!$foundBCCResque) {
                throw new \InvalidArgumentException("To use BCCResque you need to add it to composer.");
            }
        }

        $container->setParameter('dab_push_notifications.use_bcc_resque', $config['use_bcc_resque']);
        $container->setParameter('dab_push_notifications.bcc_resque_queue', $config['bcc_resque_queue']);

        $this->setAppsConfig($config);

        if (isset($config["android"])) {
            $this->setAndroidConfig($config);
            $loader->load('android.xml');
        }

        if (isset($config["apple"])) {
            $this->setAppleConfig($config);
            $loader->load('apple.xml');
        }

        if (isset($config["safari"])) {
            $this->setSafariConfig($config);
        }

        if (isset($config["blackberry"])) {
            $this->setBlackberryConfig($config);
            $loader->load('blackberry.xml');
        }

    }

    /**
     * Initial enabling
     */
    protected function setInitialParams()
    {
        $this->container->setParameter("dab_push_notifications.android.enabled", false);
        $this->container->setParameter("dab_push_notifications.apple.enabled", false);
    }

    /**
     * Sets apps config into container
     *
     * @param array $config
     * @throws \RuntimeException
     */
    protected function setAppsConfig(array $config)
    {
        if(count($config['apps']) <= 0) {
            throw new \RuntimeException(sprintf('An app is required'));
        }

        $this->container->setParameter("dab_push_notifications.apps", $config['apps']);
    }

    /**
     * Sets Android config into container
     *
     * @param array $config
     */
    protected function setAndroidConfig(array $config)
    {
        $this->container->setParameter("dab_push_notifications.android.enabled", true);

        $timeout = $config["android"]["timeout"];
        $this->container->setParameter("dab_push_notifications.android.timeout", $timeout);
        $this->container->setParameter("dab_push_notifications.android.use_multi_curl", $config["android"]["use_multi_curl"]);
        $this->container->setParameter('dab_push_notifications.android.dry_run', $config["android"]["dry_run"]);

        // GCM
        $this->container->setParameter("dab_push_notifications.android.enabled", isset($config["android"]));
        if (isset($config["android"]["api_keys"])) {
            $this->container->setParameter("dab_push_notifications.android.api_keys", $config["android"]["api_keys"]);
        }
    }

    /**
     * Sets iOS config into container
     *
     * @param array $config
     */
    protected function setAppleConfig(array $config)
    {
        // PEM file is required
        if(count($config['apple']['certificates']) <= 0) {
            throw new \RuntimeException(sprintf('A push certificate is required'));

        }

        foreach ($config['apple']['certificates'] as $appleCert) {
            if (!file_exists($appleCert['pem'])) {
                throw new \RuntimeException(sprintf('Pem file "%s" not found.', $appleCert['pem']));
            }

            if ($appleCert['json_unescaped_unicode']) {
                // Not support JSON_UNESCAPED_UNICODE option
                if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
                    throw new \LogicException(sprintf(
                        'Can\'t use JSON_UNESCAPED_UNICODE option. This option can use only PHP Version >= 5.4.0. Your version: %s',
                        PHP_VERSION
                    ));
                }
            }
        }


        $this->container->setParameter("dab_push_notifications.apple.enabled", true);
        $this->container->setParameter("dab_push_notifications.apple.certificates", $config['apple']['certificates']);
    }

    protected function setSafariConfig(array $config)
    {
        $this->container->setParameter("dab_push_notifications.safari.pem", $config['safari']['pem']);
        $this->container->setParameter("dab_push_notifications.safari.pk12", $config['safari']['pk12']);
        $this->container->setParameter("dab_push_notifications.safari.passphrase", $config['safari']['passphrase']);
        $this->container->setParameter("dab_push_notifications.safari.website_push_id", $config['safari']['website_push_id']);
        $this->container->setParameter("dab_push_notifications.safari.icon16x16", $config['safari']['icon16x16']);
        $this->container->setParameter("dab_push_notifications.safari.icon16x16@2x", $config['safari']['icon16x16@2x']);
        $this->container->setParameter("dab_push_notifications.safari.icon32x32", $config['safari']['icon32x32']);
        $this->container->setParameter("dab_push_notifications.safari.icon32x32@2x", $config['safari']['icon32x32@2x']);
        $this->container->setParameter("dab_push_notifications.safari.icon128x128", $config['safari']['icon128x128']);
        $this->container->setParameter("dab_push_notifications.safari.icon128x128@2x", $config['safari']['icon128x128@2x']);
        $this->container->setParameter("dab_push_notifications.safari.websiteName", $config['safari']['websiteName']);
        $this->container->setParameter("dab_push_notifications.safari.allowedDomains", $config['safari']['allowedDomains']);
        $this->container->setParameter("dab_push_notifications.safari.urlFormatString", $config['safari']['urlFormatString']);
        $this->container->setParameter("dab_push_notifications.safari.webServiceURL", $config['safari']['webServiceURL']);

    }

    /**
     * Sets Blackberry config into container
     *
     * @param array $config
     */
    protected function setBlackberryConfig(array $config)
    {
        $this->container->setParameter("dab_push_notifications.blackberry.enabled", true);
        $this->container->setParameter("dab_push_notifications.blackberry.evaluation", $config["blackberry"]["evaluation"]);
        $this->container->setParameter("dab_push_notifications.blackberry.app_id", $config["blackberry"]["app_id"]);
        $this->container->setParameter("dab_push_notifications.blackberry.password", $config["blackberry"]["password"]);
    }
}
