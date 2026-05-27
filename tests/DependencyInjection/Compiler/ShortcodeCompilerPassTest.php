<?php

namespace Webfactory\ShortcodeBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Thunder\Shortcode\EventContainer\EventContainer;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Webfactory\ShortcodeBundle\Controller\GuideController;
use Webfactory\ShortcodeBundle\DependencyInjection\Compiler\ShortcodeCompilerPass;

final class ShortcodeCompilerPassTest extends TestCase
{
    private ShortcodeCompilerPass $compilerPass;
    private ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->compilerPass = new ShortcodeCompilerPass();
        $this->containerBuilder = new ContainerBuilder();

        // Register the service definitions that the compiler pass always looks up
        $this->containerBuilder->setDefinition(HandlerContainer::class, new Definition(HandlerContainer::class));
        $this->containerBuilder->setDefinition(EventContainer::class, new Definition(EventContainer::class));
    }

    #[Test]
    public function tagged_services_are_added_as_handlers_to_handler_container(): void
    {
        $this->containerBuilder->register('service_id1', \stdClass::class)
            ->addTag('webfactory.shortcode', ['shortcode' => 'shortcode1']);
        $this->containerBuilder->register('service_id2', \stdClass::class)
            ->addTag('webfactory.shortcode', ['shortcode' => 'shortcode2']);

        $this->compilerPass->process($this->containerBuilder);

        $methodCalls = $this->containerBuilder->getDefinition(HandlerContainer::class)->getMethodCalls();

        $this->assertCount(2, $methodCalls);

        [$method1, $args1] = $methodCalls[0];
        $this->assertSame('add', $method1);
        $this->assertSame('shortcode1', $args1[0]);
        $this->assertInstanceOf(Reference::class, $args1[1]);
        $this->assertSame('service_id1', (string) $args1[1]);

        [$method2, $args2] = $methodCalls[1];
        $this->assertSame('add', $method2);
        $this->assertSame('shortcode2', $args2[0]);
        $this->assertInstanceOf(Reference::class, $args2[1]);
        $this->assertSame('service_id2', (string) $args2[1]);
    }

    #[Test]
    public function no_tagged_services_do_no_harm(): void
    {
        $this->compilerPass->process($this->containerBuilder);

        $this->assertCount(
            0,
            $this->containerBuilder->getDefinition(HandlerContainer::class)->getMethodCalls()
        );
    }

    #[Test]
    public function shortcode_guide_service_gets_configured_if_set(): void
    {
        $this->containerBuilder->register('service_id1', \stdClass::class)
            ->addTag('webfactory.shortcode', ['shortcode' => 'shortcode1']);
        $this->containerBuilder->register('service_id2', \stdClass::class)
            ->addTag('webfactory.shortcode', ['shortcode' => 'shortcode2']);

        $this->containerBuilder->setDefinition(GuideController::class, new Definition(GuideController::class));

        $this->compilerPass->process($this->containerBuilder);

        $this->assertSame(
            [
                ['shortcode' => 'shortcode1'],
                ['shortcode' => 'shortcode2'],
            ],
            $this->containerBuilder->getDefinition(GuideController::class)->getArgument(0)
        );
    }

    #[Test]
    public function missing_shortcode_guide_service_does_no_harm(): void
    {
        $this->compilerPass->process($this->containerBuilder);

        $this->assertFalse($this->containerBuilder->has(GuideController::class));
    }
}
