<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final class ShortcodeFacadeTest extends KernelTestCase
{
    private $fragmentHandler;

    protected static function getKernelClass(): string
    {
        return 'Webfactory\ShortcodeBundle\Tests\Fixtures\TestKernel';
    }

    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();

        // Replace fragment renderer in test kernel, as we only want to assert it is called with the correct parameters,
        // but not it's result.
        $container = static::$kernel->getContainer();
        $container->get('request_stack')->push(Request::create('/'));
        $this->fragmentHandler = $this->getMockBuilder(FragmentHandler::class)->disableOriginalConstructor()->getMock();
        $container->set('fragment.handler', $this->fragmentHandler);
    }

    /**
     * @test
     * @dataProvider shortcodeFixtures
     */
    public function processes_shortcodes(
        string $shortcode,
        ControllerReference $expectedControllerReference,
        string $expectedRenderStrategy
    ): void {
        $this->fragmentHandler->expects($this->once())
            ->method('render')
            ->willReturnCallback(
                function (
                    ControllerReference $actualControllerReference,
                    string $actualRenderStrategy
                ) use (
                    $expectedControllerReference,
                    $expectedRenderStrategy
                ) {
                    return $actualControllerReference->controller === $expectedControllerReference->controller && $actualRenderStrategy === $expectedRenderStrategy
                        ? 'OK'
                        : 'unexpected parameter values';
                }
            );

        $this->assertEquals(
            'OK',
            $this->renderTwigTemplate('{{ content | shortcodes }}', ['content' => $shortcode])
        );
    }

    public function shortcodeFixtures(): array
    {
        return [
            'esi rendering' => ['[test-esi foo=bar]', new ControllerReference('test-esi-controller', ['foo' => 'bar']), 'esi'],
            'inline rendering' => ['[test-inline bar=baz]', new ControllerReference('test-inline-controller', ['bar' => 'baz']), 'inline'],
        ];
    }

    /** @test */
    public function paragraphs_wrapping_shortcodes_get_removed(): void
    {
        $this->fragmentHandler->method('render')->willReturn('RESULT');

        $this->assertEquals(
            'RESULT',
            $this->renderTwigTemplate("{{ '<p>[test-inline]</p>' | shortcodes }}")
        );
    }

    /** @test */
    public function content_without_shortcodes_wont_be_changed(): void
    {
        $this->assertEquals(
            '<p>Content without shortcode</p>',
            $this->renderTwigTemplate("{{ '<p>Content without shortcode</p>' | shortcodes }}")
        );
    }

    private function renderTwigTemplate(string $templateCode, array $context = []): string
    {
        /** @var $container ContainerInterface */
        $container = static::$kernel->getContainer();

        /** @var \Twig_Environment $twig */
        $twig = $container->get('twig');
        $template = $twig->createTemplate($templateCode);

        return $template->render($context);
    }
}
