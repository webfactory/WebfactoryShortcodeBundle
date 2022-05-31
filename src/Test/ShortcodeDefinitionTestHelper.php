<?php

namespace Webfactory\ShortcodeBundle\Test;

use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface;
use Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler;

/**
 * Helper class that you can use in functional (end-to-end) tests to verify that a given
 * content with shortcodes is processed as expected.
 */
class ShortcodeDefinitionTestHelper
{
    /**
     * @var ControllerResolverInterface
     */
    private $controllerResolver;

    /**
     * @var HandlerContainerInterface
     */
    private $handlerContainer;

    public function __construct(ControllerResolverInterface $controllerResolver, HandlerContainerInterface $handlerContainer)
    {
        $this->handlerContainer = $handlerContainer;
        $this->controllerResolver = $controllerResolver;
    }

    public function hasShortcode(string $shortcode): bool
    {
        return null !== $this->handlerContainer->get($shortcode);
    }

    public function getHandler(string $shortcode): callable
    {
        return $this->handlerContainer->get($shortcode);
    }

    /**
     * @return callable-array
     */
    public function resolveShortcodeController(string $shortcode): array
    {
        $handler = $this->handlerContainer->get($shortcode);

        if (!$handler instanceof EmbeddedShortcodeHandler) {
            throw new RuntimeException('In order to test resolution of shortcodes to Controllers, the handler must be an instance of EmbeddedShortcodeHandler');
        }

        return $this->controllerResolver->getController(new Request([], [], ['_controller' => $handler->getControllerName()]));
    }
}
