<?php

namespace Vortexgin\YoutubeAnalyticsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class VortexginYoutubeAnalyticsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('vortexgin.youtube.analytics.channel_id', $config['channel_id']);
        $container->setParameter('vortexgin.youtube.analytics.auth_file', $config['auth_file']);
        $container->setParameter('vortexgin.youtube.analytics.config_file', $config['config_file']);
        $container->setParameter('vortexgin.youtube.analytics.callback_url', $config['callback_url']);
        $container->setParameter('vortexgin.youtube.analytics.access_token', null);

        $authConfig = @file_get_contents($config['auth_file']);
        if ($authConfig) {
            $auth = json_decode($authConfig, true);
            if (is_array($auth) && array_key_exists('access_token', $auth)) {
                $container->setParameter('vortexgin.youtube.analytics.access_token', $auth['access_token']);
            }
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
