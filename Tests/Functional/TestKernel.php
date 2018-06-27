<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Webfactory\ShortcodeBundle\DependencyInjection\WebfactoryShortcodeExtension;
use Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler;
use Webfactory\ShortcodeBundle\WebfactoryShortcodeBundle;

/**
 * A minimal kernel that is used for testing.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * ID of this kernel instance. Used to separate cache directories, containers etc.
     *
     * Null if the ID was not generated yet.
     *
     * @var string|null
     * @see getInstanceId()
     */
    private $instanceId;

    /**
     * @var array, e.g. [
     *     [
     *         'name' => 'myShortcode,
     *         'controller' => 'app.controller.myController:myShortcodePartialAction',
     *         'renderer' => 'esi'|'inline'
     *     ],
     * ]
     */
    private $shortcodeDefinitions = [];

    /**
     * @param string $environment The environment
     * @param bool   $debug       Whether to enable debugging or not
     * @param array  $shortcodeDefinitions
     */
    public function __construct($environment, $debug, array $shortcodeDefinitions)
    {
        parent::__construct($environment, $debug);
        $this->shortcodeDefinitions = $shortcodeDefinitions;
    }


    /**
     * Returns an array of bundles to register.
     *
     * @return BundleInterface[] An array of bundle instances
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new WebfactoryShortcodeBundle(),
        ];
    }

    /**
     * Configures the container.
     *
     * You can register extensions:
     *
     * $c->loadFromExtension('framework', array(
     *     'secret' => '%secret%'
     * ));
     *
     * Or services:
     *
     * $c->register('halloween', 'FooBundle\HalloweenProvider');
     *
     * Or parameters:
     *
     * $c->setParameter('halloween', 'lot of fun');
     *
     * @param ContainerBuilder $container
     * @param LoaderInterface $loader
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        // use the actual configuration of the bundle to get it's basic configuration
        (new WebfactoryShortcodeExtension())->load([], $container);

        // load some additional infrastructure
        $container->loadFromExtension(
            'framework',
            [
                'secret' => 'my-secret',
                'test' => true,
                'templating' => [
                    'engines' => ['twig']
                ]
            ]
        );

        // and register test-specific shortcodes
        foreach ($this->shortcodeDefinitions as $shortCodeDefinition) {
            $container
                ->register('webfactory.shortcode.myfilter.' . uniqid('', false), EmbeddedShortcodeHandler::class)
                ->setArguments([
                    new Reference('fragment.handler'),
                    $shortCodeDefinition['controller'],
                    $shortCodeDefinition['renderer'],
                ])->addTag('webfactory.shortcode', ['shortcode' => $shortCodeDefinition['name']]);
        }
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/test_kernel/' . $this->getInstanceId();
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        return $this->getCacheDir() . '/logs';
    }

    /**
     * Deletes the cache directory when the kernel is not used anymore.
     */
    public function __destruct()
    {
        (new Filesystem())->remove($this->getCacheDir());
    }

    /**
     * Add or import routes into your application.
     *
     *     $routes->import('config/routing.yml');
     *     $routes->add('/admin', 'AppBundle:Admin:dashboard', 'admin_dashboard');
     *
     * @param RouteCollectionBuilder $routes
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        // Use different container class names to ensure that each kernel has its own container instance.
        // If the same class name is used by multiple kernels, then a cannot redeclare class error will occur.
        return parent::getContainerClass() . $this->getInstanceId();
    }

    /**
     * Returns a unique ID for this kernel instance.
     *
     * @return string
     */
    private function getInstanceId()
    {
        if ($this->instanceId === null) {
            $this->instanceId = 'Kernel' . uniqid('', true);
            // Remove special characters from the ID to ensure that it can be used as part of a class name.
            $this->instanceId = preg_replace('/[^a-zA-Z0-9]/', '', $this->instanceId);
        }
        return $this->instanceId;
    }
}
