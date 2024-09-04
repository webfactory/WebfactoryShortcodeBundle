<?php

namespace Webfactory\ShortcodeBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Webfactory\ShortcodeBundle\WebfactoryShortcodeBundle;

final class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new WebfactoryShortcodeBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yml');
        $loader->load(__DIR__.'/config/test_shortcodes.xml');
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__.'/logs';
    }
}
