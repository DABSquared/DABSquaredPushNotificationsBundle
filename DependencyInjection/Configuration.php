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

            ->arrayNode('class')->isRequired()
            ->children()
            ->arrayNode('model')->isRequired()
            ->children()
            ->scalarNode('device')->isRequired()->end()
            ->scalarNode('message')->isRequired()->end()
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
            ->end()
            ->end()
            ->end()
            ->end()



            ->end();

        $this->addAndroid();
        $this->addiOS();
        $this->addBlackberry();



        return $treeBuilder->buildTree();
    }

    /**
     * Android configuration
     */
    protected function addAndroid()
    {
        $this->root->
            children()->
            arrayNode("android")->
            canBeUnset()->
            children()->

            // WARNING: These 3 fields as they are, outside of the c2dm array
            // are deprecrated in favour of using the c2dm array configuration
            // At present these will be overriden by anything supplied
            // in the c2dm array
            scalarNode("username")->defaultValue("")->end()->
            scalarNode("password")->defaultValue("")->end()->
            scalarNode("source")->defaultValue("")->end()->

            arrayNode("c2dm")->
            canBeUnset()->
            children()->
            scalarNode("username")->isRequired()->end()->
            scalarNode("password")->isRequired()->end()->
            scalarNode("source")->defaultValue("")->end()->
            end()->
            end()->
            arrayNode("gcm")->
            canBeUnset()->
            children()->
            scalarNode("api_key")->isRequired()->cannotBeEmpty()->end()->
            end()->
            end()->
            end()->
            end()->
            end()
        ;
    }

    /**
     * iOS configuration
     */
    protected function addiOS()
    {
        $this->root
            ->children()
                ->arrayNode("ios")
                    ->children()
                         ->arrayNode('certificates')
                            ->isRequired()
                            ->requiresAtLeastOneElement()
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('pem')->isRequired()->end()
                                    ->scalarNode("passphrase")->defaultValue("")->end()
                                    ->booleanNode("sandbox")->defaultFalse()->end()
                                    ->scalarNode('json_unescaped_unicode')->defaultFalse()->info('PHP >= 5.4.0 and each messaged must be UTF-8 encoding')->end()
                                    ->end()
                                ->end()
                            ->end()
                         ->end()
                    ->end()
                ->end();
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
