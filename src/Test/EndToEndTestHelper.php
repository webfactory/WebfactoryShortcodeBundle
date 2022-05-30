<?php

namespace Webfactory\ShortcodeBundle\Test;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Thunder\Shortcode\Processor\Processor;

/**
 * Helper class that you can use in functional (end-to-end) tests to verify that a given
 * content with shortcodes is processed as expected.
 */
class EndToEndTestHelper
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public static function createFromContainer(ContainerInterface $container): self
    {
        return new self($container->get(Processor::class), $container->get(RequestStack::class));
    }

    public function __construct(Processor $processor, RequestStack $requestStack)
    {
        $this->processor = $processor;
        $this->requestStack = $requestStack;
    }

    public function processShortcode(string $shortcode): string
    {
        // The fragment handler used by EmbeddedShortcodeHandler requires a request to be active, so let's make sure that is the case
        if (null === $this->requestStack->getCurrentRequest()) {
            $this->requestStack->push(new Request());
        }

        return $this->processor->process($shortcode);
    }
}
