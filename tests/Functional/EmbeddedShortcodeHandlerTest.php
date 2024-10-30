<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Generator;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler;
use Webfactory\ShortcodeBundle\Test\EndToEndTestHelper;
use Webfactory\ShortcodeBundle\Tests\Fixtures\Controller\ShortcodeTestController;

/**
 * Test shortcode processing using EmbeddedShortcodeHandler and a fixture ShortodeTestController,
 * to make sure shortcodes defined in various places (DI config, bundle config) work as expected.
 */
class EmbeddedShortcodeHandlerTest extends KernelTestCase
{
    /** @test */
    public function paragraphs_wrapping_shortcodes_get_removed(): void
    {
        self::assertSame('test', $this->processShortcodes('<p>[test-config-inline]</p>'));
    }

    /** @test */
    public function content_without_shortcodes_wont_be_changed(): void
    {
        self::assertSame('<p>Content without shortcode</p>', $this->processShortcodes('<p>Content without shortcode</p>'));
    }

    /**
     * @test
     *
     * @dataProvider provideShortcodeNames
     */
    public function expand_shortcodes_registered_in_different_ways(string $shortcodeName): void
    {
        // All shortcodes are set up as fixtures and use ShortcodeTestController
        self::assertSame('test foo=bar', $this->processShortcodes("[$shortcodeName foo=bar]"));
    }

    public static function provideShortcodeNames(): Generator
    {
        yield 'Inline shortcode defined in bundle config' => ['test-config-inline'];
        yield 'ESI-based shortcode defined in bundle config' => ['test-config-esi'];
        yield 'Inline shortcode defined in service definitions' => ['test-service-inline'];
        yield 'ESI-based shortcode defined in service definitions' => ['test-service-esi'];
    }

    /**
     * @test
     *
     * @dataProvider provideEsiShortcodes
     */
    public function processing_with_esi_fragments(string $shortcodeName): void
    {
        $request = new Request([], [], [], [], [], ['SCRIPT_URL' => '/', 'HTTP_HOST' => 'localhost']);
        $request->headers->set('Surrogate-Capability', 'ESI/1.0');

        self::assertStringContainsString('<esi:include ', $this->processShortcodes("[$shortcodeName foo=bar]", $request));
    }

    public static function provideEsiShortcodes(): Generator
    {
        yield 'ESI-based shortcode defined in bundle configuration' => ['test-config-esi'];
        yield 'ESI-based shortcode defined in service configuration' => ['test-service-esi'];
    }

    /** @test */
    public function invokable_controller_can_be_used(): void
    {
        self::assertSame('invokable-controller-response', $this->processShortcodes('<p>[test-config-invokable]</p>'));
    }

    /**
     * @test
     *
     * @dataProvider provideControllerNames
     */
    public function throws_exception_on_invalid_controller_names(string $controllerName): void
    {
        $this->expectException(InvalidArgumentException::class);

        new EmbeddedShortcodeHandler($this->createMock(FragmentHandler::class), $controllerName, 'inline', $this->createMock(RequestStack::class));
    }

    public static function provideControllerNames(): Generator
    {
        yield 'Empty string' => [''];
        yield 'Not existing controller' => ['Foo\Bar::baz'];
        yield 'Missing method name' => [ShortcodeTestController::class];
        yield 'Not existing method' => [ShortcodeTestController::class.'_notExistingMethod'];
        yield 'Missing class' => ['ThisClassDoesNotExist'];
        yield 'Valid reference followed by a second scope resolution operator' => [ShortcodeTestController::class.'::test::'];
    }

    private function processShortcodes(string $content, ?Request $request = null): string
    {
        self::bootKernel();

        if ($request) {
            static::getContainer()->get(RequestStack::class)->push($request);
        }

        return EndToEndTestHelper::createFromContainer(static::getContainer())->processShortcode($content);
    }
}
