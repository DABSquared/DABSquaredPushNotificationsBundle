<?php

namespace DABSquared\PushNotificationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration
{
    /**
     * @var \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    protected $root;

    /**
     * Generates the configuration tree builder.
     *
     * @return NodeInterface
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $this->root = $treeBuilder->root("dab_push_notifications");

        $this->root
            ->children()
                ->scalarNode('db_driver')->cannotBeOverwritten()->isRequired()->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
                ->scalarNode('user_entity_namespace')->defaultNull()->end()
                ->booleanNode('use_bcc_resque')->isRequired()->defaultValue(false)->end()
                ->scalarNode('bcc_resque_queue')->defaultValue("dab-push-notifications")->end()
                ->arrayNode('class')->isRequired()
                    ->children()
                        ->arrayNode('model')->isRequired()
                            ->children()
                                ->scalarNode('device')->isRequired()->end()
                                ->scalarNode('message')->isRequired()->end()
                                ->scalarNode('appevent')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('service')->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('manager')->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('device')->cannotBeEmpty()->defaultValue('dab_push_notifications.manager.device.default')->end()
                                ->scalarNode('message')->cannotBeEmpty()->defaultValue('dab_push_notifications.manager.message.default')->end()
                                ->scalarNode('appevent')->cannotBeEmpty()->defaultValue('dab_push_notifications.manager.appevent.default')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        $this->addApps();
        $this->addAndroid();
        $this->addApple();
        $this->addBlackberry();
        $this->addSafari();

        return $treeBuilder->buildTree();
    }

    /**
     * Apps configuration
     */
    protected function addApps()
    {
        $this->root
            ->children()
                ->arrayNode("apps")
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->isRequired()->end()
                            ->scalarNode('internal_app_id')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Android configuration
     */
    protected function addAndroid()
    {
        $this->root->
        children()->
        arrayNode("android")->
        children()->
        scalarNode("timeout")->defaultValue(5)->end()->
        booleanNode("use_multi_curl")->defaultValue(true)->end()->
        booleanNode("dry_run")->defaultFalse()->end()->
        arrayNode('api_keys')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
            ->scalarNode('api_key')->isRequired()->end()
            ->scalarNode('internal_app_id')->isRequired()->end()
            ->end()->
            end()->
            end()
        ;
    }

    /**
     * Apple configuration
     */
    protected function addApple()
    {
        $this->root
            ->children()
                ->arrayNode("apple")
                    ->children()
                         ->arrayNode('certificates')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('pem')->isRequired()->end()
                                    ->scalarNode("passphrase")->defaultValue("")->end()
                                    ->scalarNode('internal_app_id')->isRequired()->end()
                                    ->booleanNode("sandbox")->defaultFalse()->end()
                                    ->scalarNode('json_unescaped_unicode')->defaultFalse()->info('PHP >= 5.4.0 and each messaged must be UTF-8 encoding')->end()
                                ->end()
                            ->end()
                         ->end()
                     ->end()
                ->end()
            ->end()
        ;
    }


    /**
     * Safari configuration
     */
    protected function addSafari()
    {
        $this->root
            ->children()
                ->arrayNode("safari")
                    ->children()
                        ->scalarNode('pem')->isRequired()->end()
                        ->scalarNode('pk12')->isRequired()->end()
                        ->scalarNode("passphrase")->defaultValue("")->end()
                        ->scalarNode('website_push_id')->isRequired()->end()
                        ->scalarNode('icon16x16')->isRequired()->end()
                        ->scalarNode('icon16x16@2x')->isRequired()->end()
                        ->scalarNode('icon32x32')->isRequired()->end()
                        ->scalarNode('icon32x32@2x')->isRequired()->end()
                        ->scalarNode('icon128x128')->isRequired()->end()
                        ->scalarNode('icon128x128@2x')->isRequired()->end()
                        ->scalarNode('websiteName')->isRequired()->end()
                        ->arrayNode('allowedDomains')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->scalarNode('urlFormatString')->isRequired()->end()
                        ->scalarNode('webServiceURL')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Blackberry configuration
     */
    protected function addBlackberry()
    {
        $this->root->
            children()->
                arrayNode("blackberry")->
                    children()->
                        booleanNode("evaluation")->defaultFalse()->end()->
                        scalarNode("app_id")->isRequired()->cannotBeEmpty()->end()->
                        scalarNode("password")->isRequired()->cannotBeEmpty()->end()->
                    end()->
                end()->
            end()
        ;
    }
}
