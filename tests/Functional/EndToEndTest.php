<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Test that given test shortcodes are found in a Twig template and being replaced with the
 * corresponding test controller's response content.
 */
class EndToEndTest extends KernelTestCase
{
    /** @test */
    public function paragraphs_wrapping_shortcodes_get_removed(): void
    {
        $this->assertEquals(
            'test',
            $this->renderTwig("{{ '<p>[test-config-inline]</p>' | shortcodes }}")
        );
    }

    /** @test */
    public function content_without_shortcodes_wont_be_changed(): void
    {
        $this->assertEquals(
            '<p>Content without shortcode</p>',
            $this->renderTwig("{{ '<p>Content without shortcode</p>' | shortcodes }}")
        );
    }

    /**
     * @test
     * @dataProvider provideShortcodeNames
     */
    public function expand_shortcode_in_Twig(string $shortcodeName): void
    {
        $result = $this->renderTwig('{{ content | shortcodes }}', ['content' => "[$shortcodeName foo=bar]"]);

        self::assertSame('test foo=bar', $result);
    }

    public function provideShortcodeNames(): \Generator
    {
        yield 'Inline shortcode defined in bundle config' => ['test-config-inline'];
        yield 'ESI-based shortcode defined in bundle config' => ['test-config-esi'];
        yield 'Inline shortcode defined in service definitions' => ['test-service-inline'];
        yield 'ESI-based shortcode defined in service definitions' => ['test-service-esi'];
    }

    /**
     * @test
     * @dataProvider provideEsiShortcodes
     */
    public function uses_ESI_fragments(string $shortcodeName): void
    {
        $request = new Request([], [], [], [], [], ['SCRIPT_URL' => '/', 'HTTP_HOST' => 'localhost']);
        $request->headers->set('Surrogate-Capability', 'ESI/1.0');

        $result = $this->renderTwig('{{ content | shortcodes }}', ['content' => "[$shortcodeName foo=bar]"], $request);

        self::assertStringContainsString('<esi:include ', $result);
    }

    public function provideEsiShortcodes(): \Generator
    {
        yield 'ESI-based shortcode defined in bundle configuration' => ['test-config-esi'];
        yield 'ESI-based shortcode defined in service configuration' => ['test-service-esi'];
    }

    private function renderTwig(string $templateCode, array $context = [], Request $request = null): string
    {
        self::bootKernel();

        $container = static::getContainer();

        $requestStack = $container->get(RequestStack::class);
        $requestStack->push($request ?? new Request());

        $twig = $container->get('twig');

        $template = $twig->createTemplate($templateCode);
        return $twig->render($template, $context);
    }
}
