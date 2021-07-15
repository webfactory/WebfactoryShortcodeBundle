<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Loads the bundle configuration.
 */
final class WebfactoryShortcodeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('shortcodes.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias('webfactory_shortcode.parser', 'webfactory_shortcode.'.$config['parser'].'_parser');

        $container->setParameter('webfactory_shortcode.recursion_depth', $config['recursion_depth']);
        $container->setParameter('webfactory_shortcode.max_iterations', $config['max_iterations']);
    }
}
