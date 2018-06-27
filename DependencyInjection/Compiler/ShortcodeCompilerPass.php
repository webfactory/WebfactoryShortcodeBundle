<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CompilerPass that prepares the shortcode facade and the GuideController (if configured).
 */
class ShortcodeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $shortcodeServices = $container->findTaggedServiceIds('webfactory.shortcode');

        // add services tagged with webfactory.shortcode.facade as handlers to the short code facade
        $serviceDefinition = $container->findDefinition('webfactory.shortcode.facade');
        foreach ($shortcodeServices as $id => $shortcodeTags) {
            foreach ($shortcodeTags as $shortcodeTag) {
                $serviceDefinition->addMethodCall('addHandler', [$shortcodeTag['shortcode'], new Reference($id)]);
            }
        }

        // prepare the GuideController if it's configuration is imported
        if ($container->has('webfactory.shortcode.guide.controller')) {
            $allShortcodeTags = [];
            foreach ($shortcodeServices as $id => $shortcodeTags) {
                $allShortcodeTags += $shortcodeTags;
            }

            $container
                ->getDefinition('webfactory.shortcode.guide.controller')
                ->setArgument(0, $allShortcodeTags);
        }
    }
}
