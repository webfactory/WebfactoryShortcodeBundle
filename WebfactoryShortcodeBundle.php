<?php

namespace Webfactory\ShortcodeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webfactory\ShortcodeBundle\DependencyInjection\Compiler\ShortcodeCompilerPass;

/**
 * Symfony bundle class.
 */
final class WebfactoryShortcodeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ShortcodeCompilerPass());
    }
}
