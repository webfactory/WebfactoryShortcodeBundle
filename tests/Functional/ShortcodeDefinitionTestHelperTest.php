<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Thunder\Shortcode\Handler\PlaceholderHandler;
use Webfactory\ShortcodeBundle\Test\ShortcodeDefinitionTestHelper;
use Webfactory\ShortcodeBundle\Tests\Fixtures\Controller\ShortcodeTestController;

class ShortcodeDefinitionTestHelperTest extends KernelTestCase
{
    /**
     * @var ShortcodeDefinitionTestHelper
     */
    private $helper;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->helper = static::getContainer()->get(ShortcodeDefinitionTestHelper::class);
    }

    /**
     * @test
     */
    public function throws_exception_for_handlers_that_do_not_use_controllers(): void
    {
        self::expectException(RuntimeException::class);
        $this->helper->resolveShortcodeController('placeholder'); // uses the \Thunder\Shortcode\Handler\PlaceholderHandler handler class directly
    }

    /**
     * @test
     */
    public function can_test_whether_shortcode_is_defined(): void
    {
        self::assertTrue($this->helper->hasShortcode('placeholder'));
        self::assertFalse($this->helper->hasShortcode('unknown-shortcode'));
    }

    /**
     * @test
     */
    public function can_retrieve_handler(): void
    {
        self::assertInstanceOf(PlaceholderHandler::class, $this->helper->getHandler('placeholder'));
    }

    /**
     * @test
     */
    public function can_be_used_to_assert_controller_class_and_method(): void
    {
        $controller = $this->helper->resolveShortcodeController('test-config-inline');

        self::assertInstanceOf(ShortcodeTestController::class, $controller[0]);
        self::assertSame('test', $controller[1]);
    }
}
