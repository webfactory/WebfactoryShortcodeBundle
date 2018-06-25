<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final class ShortcodeTest extends ShortcodeTestCase
{
    /** @test */
    public function shortcode_leads_to_rendering_of_controller_reference()
    {
        static::$shortcodesToRegister = [
            [
                'controller' => 'app.controller.myFilter:myfilterPartialAction',
                'renderer' => 'esi',
                'name' => 'myShortcode',
            ]
        ];
        static::bootKernel();

        // Replace fragment renderer in test kernel, as we only want to assert it is called with the correct parameters,
        // but not it's result.
        $container = static::$kernel->getContainer();
        $fragmentHandler = $this->getMockBuilder(FragmentHandler::class)->disableOriginalConstructor()->getMock();
        $fragmentHandler->expects($this->once())
            ->method('render')
            ->with(new ControllerReference('app.controller.myFilter:myfilterPartialAction', ['id' => 42]), 'esi')
            ->willReturn($mockedRenderResult = 'OK');
        $container->set('fragment.handler', $fragmentHandler);

        $this->assertEquals(
            $mockedRenderResult,
            $this->renderTwigTemplate('{{ content |shortcodes }}', ['content' => '[myShortcode id=42]'])
        );        
    }

    /** @test */
    public function paragraphs_wrapping_shortcodes_get_removed()
    {
        static::$shortcodesToRegister = [
            [
                'controller' => 'app.controller.myFilter:myfilterPartialAction',
                'renderer' => 'esi',
                'name' => 'myShortcode',
            ]
        ];
        static::bootKernel();

        // Replace fragment renderer in test kernel, as we only want to assert it is called with the correct parameters,
        // but not it's result.
        $container = static::$kernel->getContainer();
        $fragmentHandler = $this->getMockBuilder(FragmentHandler::class)->disableOriginalConstructor()->getMock();
        $fragmentHandler->expects($this->once())
            ->method('render')
            ->with(new ControllerReference('app.controller.myFilter:myfilterPartialAction', ['id' => 42]), 'esi')
            ->willReturn($mockedRenderResult = 'OK');
        $container->set('fragment.handler', $fragmentHandler);

        $this->assertEquals(
            $mockedRenderResult,
            $this->renderTwigTemplate('{{ content |shortcodes }}', ['content' => '<p> [myShortcode id=42] </p>'])
        );
    }

    /** @test */
    public function content_without_shortcodes_wont_be_changed()
    {
        static::bootKernel();

        $this->assertEquals(
            '<p>Content without shortcode</p>',
            $this->renderTwigTemplate('{{ \'<p>Content without shortcode</p>\' | shortcodes }}', [])
        );
    }
}
