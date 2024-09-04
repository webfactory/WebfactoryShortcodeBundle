<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Loads the bundle configuration.
 */
final class WebfactoryShortcodeExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('shortcodes.xml');
        $loader->load('guide.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setAlias('webfactory_shortcode.parser', 'webfactory_shortcode.'.$config['parser'].'_parser');

        $container->setParameter('webfactory_shortcode.recursion_depth', $config['recursion_depth']);
        $container->setParameter('webfactory_shortcode.max_iterations', $config['max_iterations']);

        foreach ($config['shortcodes'] as $shortcodeName => $shortcodeDefinition) {
            $definition = new ChildDefinition('Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.'.$shortcodeDefinition['method']);
            $definition->replaceArgument(1, $shortcodeDefinition['controller']);
            $definition->addTag('webfactory.shortcode', ['shortcode' => $shortcodeName, 'description' => $shortcodeDefinition['description'], 'example' => $shortcodeDefinition['example']]);
            $container->setDefinition('webfactory_shortcode.handler.'.$shortcodeName, $definition);
        }
    }
}
