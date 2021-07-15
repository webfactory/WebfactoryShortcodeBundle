<?php

namespace Webfactory\ShortcodeBundle\Tests\Fixtures;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Make fragement.handler public so it can be mocked in tests.
 */
class PublicFragmentHandlerClass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $container->getDefinition('fragment.handler')->setPublic(true);
    }
}
