<?php

namespace Webfactory\ShortcodeBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Webfactory\ShortcodeBundle\Controller\GuideController;
use Webfactory\ShortcodeBundle\DependencyInjection\Compiler\ShortcodeCompilerPass;

final class ShortcodeCompilerPassTest extends TestCase
{
    /**
     * System under test.
     *
     * @var ShortcodeCompilerPass
     */
    private $compilerPass;

    /** @var ContainerBuilder|MockObject */
    private $containerBuilder;

    protected function setUp(): void
    {
        $this->compilerPass = new ShortcodeCompilerPass();
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /** @test */
    public function tagged_services_are_added_as_handlers_to_handler_container(): void
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([
                'service_id1' => [
                    ['shortcode' => 'shortcode1'],
                ],
                'service_id2' => [
                    ['shortcode' => 'shortcode2'],
                ],
            ]);

        $mockedShortcodeHandlerContainer = $this->createMock(Definition::class);
        $mockedShortcodeHandlerContainer->expects($this->exactly(2))
            ->method('addMethodCall')
            ->with('add', $this->callback(function (array $argument) {
                static $count = 0;
                ++$count;
                return 'shortcode'.$count === $argument[0] && $argument[1] instanceof Reference;
            }));

        $this->containerBuilder->expects($this->once())
            ->method('findDefinition')
            ->with(HandlerContainer::class)
            ->willReturn($mockedShortcodeHandlerContainer);

        $this->compilerPass->process($this->containerBuilder);
    }

    /** @test */
    public function no_tagged_services_do_no_harm(): void
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([]);

        $this->compilerPass->process($this->containerBuilder);
    }

    /** @test */
    public function shortcode_guide_service_gets_configured_if_set(): void
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([
                'service_id1' => [
                    ['shortcode' => 'shortcode1'],
                ],
                'service_id2' => [
                    ['shortcode' => 'shortcode2'],
                ],
            ]);

        $this->containerBuilder->expects($this->once())
            ->method('findDefinition')
            ->with(HandlerContainer::class)
            ->willReturn($this->createMock(Definition::class));

        $this->containerBuilder->expects($this->once())
            ->method('has')
            ->with(GuideController::class)
            ->willReturn(true);

        $mockedShortcodeGuideServiceDefinition = $this->createMock(Definition::class);
        $mockedShortcodeGuideServiceDefinition->expects($this->once())
            ->method('setArgument')
            ->with(
                0,
                [
                    ['shortcode' => 'shortcode1'],
                    ['shortcode' => 'shortcode2'],
                ]
            );

        $this->containerBuilder->expects($this->once())
            ->method('getDefinition')
            ->with(GuideController::class)
            ->willReturn($mockedShortcodeGuideServiceDefinition);

        $this->compilerPass->process($this->containerBuilder);
    }

    /** @test */
    public function missing_shortcode_guide_service_does_no_harm(): void
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([]);

        $this->containerBuilder->expects($this->once())
            ->method('has')
            ->with(GuideController::class)
            ->willReturn(false);

        $this->compilerPass->process($this->containerBuilder);
    }
}
