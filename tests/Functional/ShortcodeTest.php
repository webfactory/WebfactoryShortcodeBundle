<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

@trigger_error(sprintf('The "%s" class is deprecated, use "%s" instead.', ShortcodeTest::class, \Webfactory\ShortcodeBundle\Test\ShortcodeTest::class), \E_USER_DEPRECATED);

/**
 * Abstract template for common shortcode tests.
 *
 * @deprecated Use \Webfactory\ShortcodeBundle\Test\ShortcodeTest instead
 */
abstract class ShortcodeTest extends \Webfactory\ShortcodeBundle\Test\ShortcodeTest
{
}
