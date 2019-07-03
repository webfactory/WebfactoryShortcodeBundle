<?php

namespace Webfactory\ShortcodeBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Webfactory\ShortcodeBundle\DependencyInjection\Compiler\ShortcodeCompilerPass;

final class ShortcodeCompilerPassTest extends TestCase
{
    /**
     * System under test.
     *
     * @var ShortcodeCompilerPass
     */
    private $compilerPass;

    /** @var ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject */
    private $containerBuilder;

    protected function setUp()
    {
        $this->compilerPass = new ShortcodeCompilerPass();
        $this->containerBuilder = $this->getMockBuilder(ContainerBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /** @test */
    public function tagged_services_are_added_as_handlers_to_facade()
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

        $mockedShortcodeFacade = $this->createMock(Definition::class);
        $mockedShortcodeFacade->expects($this->at(0))
            ->method('addMethodCall')
            ->with('addHandler', $this->callback(function (array $argument) {
                return 'shortcode1' === $argument[0]
                    && $argument[1] instanceof Reference;
            }));
        $mockedShortcodeFacade->expects($this->at(1))
            ->method('addMethodCall')
            ->with('addHandler', $this->callback(function ($argument) {
                return 'shortcode2' === $argument[0]
                    && $argument[1] instanceof Reference;
            }));

        $this->containerBuilder->expects($this->once())
            ->method('findDefinition')
            ->with('webfactory.shortcode.facade')
            ->willReturn($mockedShortcodeFacade);

        $this->compilerPass->process($this->containerBuilder);
    }

    /** @test */
    public function no_tagged_services_do_no_harm()
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([]);

        $this->compilerPass->process($this->containerBuilder);

        $this->assertTrue(true);
    }

    /** @test */
    public function shortcode_guide_service_gets_configured_if_set()
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
            ->with('webfactory.shortcode.facade')
            ->willReturn($this->createMock(Definition::class));

        $this->containerBuilder->expects($this->once())
            ->method('has')
            ->with('webfactory.shortcode.guide.controller')
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
            ->with('webfactory.shortcode.guide.controller')
            ->willReturn($mockedShortcodeGuideServiceDefinition);

        $this->compilerPass->process($this->containerBuilder);
    }

    /** @test */
    public function missing_shortcode_guide_service_does_no_harm()
    {
        $this->containerBuilder->expects($this->once())
            ->method('findTaggedServiceIds')
            ->willReturn([]);

        $this->containerBuilder->expects($this->once())
            ->method('has')
            ->with('webfactory.shortcode.guide.controller')
            ->willReturn(false);

        $this->compilerPass->process($this->containerBuilder);

        $this->assertTrue(true);
    }
}
