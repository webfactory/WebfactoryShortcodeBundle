<?php

namespace Webfactory\ShortcodeBundle\Handler;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
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
     * @param FragmentHandler $fragmentHandler
     * @param string $controllerName
     * @param string $renderer
     * @param LoggerInterface $logger
     * @param RequestStack $requestStack
     */
    public function __construct(
        FragmentHandler $fragmentHandler,
        $controllerName,
        $renderer,
        LoggerInterface $logger = null,
        RequestStack $requestStack
    ) {
        $this->fragmentHandler = $fragmentHandler;
        $this->controllerName = $controllerName;
        $this->renderer = $renderer;
        $this->logger = $logger ?: new NullLogger();
        $this->requestStack = $requestStack;
    }

    /**
     * @param ShortcodeInterface $shortcode
     * @return null|string
     */
    public function __invoke(ShortcodeInterface $shortcode)
    {
        $this->logger->notice(
            'Request {controllerName} with parameters {parameters} and renderer {renderer} to resolve shortcode {shortcode}, triggered by a request to {url}.',
            [
                'controllerName' => $this->controllerName,
                'parameters' => json_encode($shortcode->getParameters()),
                'renderer' => $this->renderer,
                'shortcode' => $shortcode->getName(),
                'url' => $this->requestStack->getMasterRequest()->getRequestUri(),
            ]
        );

        return $this->fragmentHandler->render(
            new ControllerReference($this->controllerName, $shortcode->getParameters()),
            $this->renderer
        );
    }
}
