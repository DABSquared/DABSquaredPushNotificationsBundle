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
     *
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
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
        $container->setParameter('dab_push_notifications.model_manager_name', $config['model_manager_name']);


        // handle the MongoDB document manager name in a specific way as it does not have a registry to make it easy
        // TODO: change it if https://github.com/symfony/DoctrineMongoDBBundle/pull/31 is merged
        if ('mongodb' === $config['db_driver']) {
            if (null === $config['model_manager_name']) {
                $container->setAlias('dab_push_notifications.document_manager', new Alias('doctrine.odm.mongodb.document_manager', false));
            } else {
                $container->setAlias('dab_push_notifications.document_manager', new Alias(sprintf('doctrine.odm.%s_mongodb.document_manager', $config['model_manager_name']), false));
            }
        }

        $container->setAlias('dab_push_notifications.manager.device', $config['service']['manager']['device']);
        $container->setAlias('dab_push_notifications.manager.message', $config['service']['manager']['message']);

        $this->setInitialParams();
        if (isset($config["android"])) {
            $this->setAndroidConfig($config);
            $loader->load('android.xml');
        }
        if (isset($config["ios"])) {
            $this->setiOSConfig($config);
            $loader->load('ios.xml');
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
        $this->container->setParameter("dab_push_notifications.ios.enabled", false);
    }

    /**
     * Sets Android config into container
     *
     * @param array $config
     */
    protected function setAndroidConfig(array $config)
    {
        $this->container->setParameter("dab_push_notifications.android.enabled", true);
        $this->container->setParameter("dab_push_notifications.android.c2dm.enabled", true);

        // C2DM
        $username = $config["android"]["username"];
        $password = $config["android"]["password"];
        $source = $config["android"]["source"];
        if (isset($config["android"]["c2dm"])) {
            $username = $config["android"]["c2dm"]["username"];
            $password = $config["android"]["c2dm"]["password"];
            $source = $config["android"]["c2dm"]["source"];
        }
        $this->container->setParameter("dab_push_notifications.android.c2dm.username", $username);
        $this->container->setParameter("dab_push_notifications.android.c2dm.password", $password);
        $this->container->setParameter("dab_push_notifications.android.c2dm.source", $source);

        // GCM
        $this->container->setParameter("dab_push_notifications.android.gcm.enabled", isset($config["android"]["gcm"]));
        if (isset($config["android"]["gcm"])) {
            $this->container->setParameter("dab_push_notifications.android.gcm.api_key", $config["android"]["gcm"]["api_key"]);
        }
    }

    /**
     * Sets iOS config into container
     *
     * @param array $config
     */
    protected function setiOSConfig(array $config)
    {
        // PEM file is required
        if(count($config['ios']['certificates']) <= 0) {
            throw new \RuntimeException(sprintf('A push certificate is required'));

        }

        foreach ($config['ios']['certificates'] as $iosCert) {
            if (!file_exists($iosCert['pem'])) {
                throw new \RuntimeException(sprintf('Pem file "%s" not found.', $iosCert['pem']));
            }

            if ($iosCert['json_unescaped_unicode']) {
                // Not support JSON_UNESCAPED_UNICODE option
                if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
                    throw new \LogicException(sprintf(
                        'Can\'t use JSON_UNESCAPED_UNICODE option. This option can use only PHP Version >= 5.4.0. Your version: %s',
                        PHP_VERSION
                    ));
                }
            }
        }


        $this->container->setParameter("dab_push_notifications.ios.enabled", true);
        $this->container->setParameter("dab_push_notifications.ios.certificates", $config['ios']['certificates']);
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
