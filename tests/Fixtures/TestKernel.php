<?php

namespace Webfactory\ShortcodeBundle\Tests\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Thunder\Shortcode\Handler\PlaceholderHandler;
use Webfactory\ShortcodeBundle\Tests\Fixtures\Controller\InvokableShortcodeTestController;
use Webfactory\ShortcodeBundle\Tests\Fixtures\Controller\ShortcodeTestController;
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
        $loader->load(static function (ContainerBuilder $container): void {
            $container->loadFromExtension('framework', [
                'secret' => 'top-secret',
                'test' => true,
                'esi' => ['enabled' => true],
                'fragments' => ['enabled' => true],
                'router' => ['resource' => '%kernel.project_dir%/src/Resources/config/guide-routing.php'],
            ] + (Kernel::VERSION_ID < 70000 ? ['annotations' => ['enabled' => false]] : []));

            $container->loadFromExtension('twig', [
                'strict_variables' => true,
            ]);

            $testControllerAction = ShortcodeTestController::class.'::test';

            $container->loadFromExtension('webfactory_shortcode', [
                'remove_pending_inner_paragraph_elements' => true,
                'shortcodes' => [
                    'test-config-inline' => $testControllerAction,
                    'test-config-esi' => [
                        'controller' => $testControllerAction,
                        'method' => 'esi',
                    ],
                    'test-config-invokable' => InvokableShortcodeTestController::class,
                    'test-shortcode-guide' => [
                        'controller' => $testControllerAction,
                        'description' => "Description for the 'test-shortcode-guide' shortcode",
                        'example' => 'test-shortcode-guide test=true',
                    ],
                ],
            ]);

            $container->register(ShortcodeTestController::class)
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->addTag('controller.service_arguments');

            $container->register(InvokableShortcodeTestController::class)
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->addTag('controller.service_arguments');

            // Service definitions from test_shortcodes.xml
            $container->setDefinition('test_esi', new ChildDefinition('Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.esi'))
                ->replaceArgument(1, $testControllerAction)
                ->addTag('webfactory.shortcode', ['shortcode' => 'test-service-esi']);

            $container->setDefinition('test_inline', new ChildDefinition('Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline'))
                ->replaceArgument(1, $testControllerAction)
                ->addTag('webfactory.shortcode', ['shortcode' => 'test-service-inline']);

            $container->register(PlaceholderHandler::class)
                ->addTag('webfactory.shortcode', ['shortcode' => 'placeholder']);
        });
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__.'/var/log';
    }
}
