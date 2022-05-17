<?php

namespace Webfactory\ShortcodeBundle\Handler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Throwable;
use Thunder\Shortcode\Shortcode\ProcessedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * Handler for thunderer\Shortcode that embeds the configured renderer for a shortcode.
 *
 * The attributes of the shortcode will be passed on as parameters to the controller.
 */
class EmbeddedShortcodeHandler
{
    /** @var FragmentHandler */
    private $fragmentHandler;

    /** @var string */
    private $controllerName;

    /** @var string */
    private $renderer;

    /** @var LoggerInterface */
    private $logger;

    /** @var RequestStack */
    private $requestStack;

    /**
     * @param string          $controllerName
     * @param string          $renderer
     * @param LoggerInterface $logger
     */
    public function __construct(
        FragmentHandler $fragmentHandler,
        $controllerName,
        $renderer,
        RequestStack $requestStack,
        LoggerInterface $logger = null
    ) {
        $this->fragmentHandler = $fragmentHandler;
        $this->controllerName = $controllerName;
        $this->renderer = $renderer;
        $this->requestStack = $requestStack;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return string|null
     */
    public function __invoke(ShortcodeInterface $shortcode)
    {
        $this->logger->info(
            'Request {controllerName} with parameters {parameters} and renderer {renderer} to resolve shortcode {shortcode}, triggered by a request to {url}.',
            [
                'controllerName' => $this->controllerName,
                'parameters' => json_encode($shortcode->getParameters()),
                'renderer' => $this->renderer,
                'shortcode' => $shortcode->getName(),
                'url' => $this->requestStack->getMasterRequest() ? $this->requestStack->getMasterRequest()->getRequestUri() : '-',
            ]
        );

        try {
            return $this->fragmentHandler->render(
                new ControllerReference($this->controllerName, $shortcode->getParameters()),
                $this->renderer
            );
        } catch (Throwable $exception) {
            $this->logger->error('An exception was thrown when rendering the shortcode', ['exception' => $exception]);

            if ($shortcode instanceof ProcessedShortcode) {
                $text = trim($shortcode->getShortcodeText(), '[]');
            } else {
                $text = $shortcode->getName().' ...';
            }

            // Avoid an infinite loop that occurs if the original shortcode is returned
            return "<code>&#91;$text&#93;</code>";
        }
    }
}
