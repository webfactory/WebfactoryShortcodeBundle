<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CompilerPass that adds services tagged with webfactory.shortcode.facade as handlers to the short code facade.
 */
class ShortcodeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceDefinition = $container->findDefinition('webfactory.shortcode.facade');

        foreach ($container->findTaggedServiceIds('webfactory.shortcode') as $id => $tags) {
            foreach ($tags as $attributes) {
                $serviceDefinition->addMethodCall('addHandler', [$attributes['shortcode'], new Reference($id)]);
            }
        }
    }
}
