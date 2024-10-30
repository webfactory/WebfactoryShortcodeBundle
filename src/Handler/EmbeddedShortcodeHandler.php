<?php

namespace Webfactory\ShortcodeBundle\Handler;

use InvalidArgumentException;
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

    public function __construct(
        FragmentHandler $fragmentHandler,
        string $controllerName,
        string $renderer,
        RequestStack $requestStack,
        ?LoggerInterface $logger = null
    ) {
        $this->validateControllerName($controllerName);

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
                'url' => $this->requestStack->getMainRequest() ? $this->requestStack->getMainRequest()->getRequestUri() : '-',
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

    public function getControllerName(): string
    {
        return $this->controllerName;
    }

    private function validateControllerName(string $controllerName): void
    {
        if (class_exists($controllerName)) {
            // Check with method_exists instead of is_callable, because is_callable would need an object instance to
            // positively test an invokable classes
            if (method_exists($controllerName, '__invoke')) {
                return;
            }

            throw new InvalidArgumentException('The configured controller "'.$controllerName.'" does not refer a method. Although a class "'.$controllerName.'" exists, but has no __invoke method.');
        }

        $callableFragments = explode('::', $controllerName);
        if (!\is_array($callableFragments) || !isset($callableFragments[1]) || !method_exists($callableFragments[0], $callableFragments[1])) {
            throw new InvalidArgumentException('The controller method: "'.$controllerName.'" does not exist.');
        }
    }
}
