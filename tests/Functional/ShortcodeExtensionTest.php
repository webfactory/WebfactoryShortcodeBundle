<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Test that integration with Twig works as expected.
 */
class ShortcodeExtensionTest extends KernelTestCase
{
    /**
     * @test
     */
    public function resolve_placeholder_shortcode_in_twig(): void
    {
        self::assertSame('News from year 1970.', $this->renderTemplate('{% apply shortcodes %}[placeholder year=1970]News from year %year%.[/placeholder]{% endapply %}'));
    }

    /**
     * @test
     */
    public function filter_returns_safe_html(): void
    {
        self::assertSame('<p>HTML</p>', $this->renderTemplate('{{ "<p>HTML</p>" | shortcodes }}'));
    }

    private function renderTemplate(string $template): string
    {
        self::bootKernel();
        $container = static::$container;

        $twig = $container->get('twig');

        $template = $twig->createTemplate($template);

        return $twig->render($template);
    }
}
