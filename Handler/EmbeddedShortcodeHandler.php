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
     * @param ShortcodeInterface $shortcode
     *
     * @return string|null
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

        try {
            return $this->fragmentHandler->render(
                new ControllerReference(
                    $this->controllerName,
                    array_merge(['request' => $this->requestStack->getCurrentRequest()], $shortcode->getParameters())
                ),
                $this->renderer
            );
        } catch (\InvalidArgumentException $exception) {
            if ($this->renderer === 'esi') {
                throw new \InvalidArgumentException(
                    'An InvalidArgumentException occured while trying to render the shortcode '
                    .$shortcode->getShortcodeText().'. You\'ve probably tried to use the ESI rendering  strategy for '
                    .'your shortcodes while handling a request that contained non-scalar values as part of URI '
                    .'attributes. This can happen e.g. when using Param Converters for your original controller '
                    .'action, as the request (containing the conversion result) is automatically passed to the call of '
                    .'the shortcode controller to allow context sensitive shortcodes. You could use '
                    .'webfactory.shortcode.embed_inline_for_shortcode_handler as parent in your shortcode\'s service '
                    .'defintion, so that the inline instead of ESI rendering strategy will be used.',
                    0,
                    $exception
                );
            }

            throw $exception;
        }
    }
}
