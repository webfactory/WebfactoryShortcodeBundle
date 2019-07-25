<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * CompilerPass that prepares the shortcode handler container and the GuideController (if configured).
 */
class ShortcodeCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $shortcodeServices = $container->findTaggedServiceIds('webfactory.shortcode');

        // add services tagged with webfactory.shortcode as handlers to the short code handler container
        $handlerContainer = $container->findDefinition('webfactory.shortcode.handler_container');
        foreach ($shortcodeServices as $id => $shortcodeTags) {
            foreach ($shortcodeTags as $shortcodeTag) {
                $handlerContainer->addMethodCall('add', [$shortcodeTag['shortcode'], new Reference($id)]);
            }
        }

        // prepare the GuideController if it's configuration is imported
        if ($container->has('webfactory.shortcode.guide.controller')) {
            $allShortcodeTags = [];
            foreach ($shortcodeServices as $id => $shortcodeTags) {
                $allShortcodeTags = array_merge($allShortcodeTags, $shortcodeTags);
            }

            $container
                ->getDefinition('webfactory.shortcode.guide.controller')
                ->setArgument(0, $allShortcodeTags);
        }
    }
}
