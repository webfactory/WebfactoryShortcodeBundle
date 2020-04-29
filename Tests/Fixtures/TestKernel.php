<?php

namespace Webfactory\ShortcodeBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Webfactory\ShortcodeBundle\WebfactoryShortcodeBundle;

/**
 * A minimal kernel that is used for testing.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @return BundleInterface[] An array of bundle instances
     */
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new WebfactoryShortcodeBundle(),
        ];
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config.yml');
        $loader->load(__DIR__.'/config/test_shortcodes.xml');
        $container->addCompilerPass(new PublicFragmentHandlerClass());
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__.'/logs';
    }

    /**
     * Add or import routes into your application.
     *
     *     $routes->import('config/routing.yml');
     *     $routes->add('/admin', 'AppBundle:Admin:dashboard', 'admin_dashboard');
     *
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
    }
}
