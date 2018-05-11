<?php

namespace Vortexgin\YoutubeAnalyticsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('vortexgin_youtube_analytics');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('channel_id')
                    ->isRequired()
                ->end()
                ->scalarNode('auth_file')
                    ->isRequired()
                ->end()
                ->scalarNode('config_file')
                    ->isRequired()
                ->end()
                ->scalarNode('callback_url')
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}