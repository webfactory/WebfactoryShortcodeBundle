<?php

namespace Webfactory\ShortcodeBundle\Handler;

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

    /**
     * @param FragmentHandler $fragmentHandler
     * @param string $controllerName
     * @param string $renderer
     */
    public function __construct(FragmentHandler $fragmentHandler, $controllerName, $renderer)
    {
        $this->fragmentHandler = $fragmentHandler;
        $this->controllerName = $controllerName;
        $this->renderer = $renderer;
    }

    /**
     * @param ShortcodeInterface $shortcode
     * @return null|string
     */
    public function __invoke(ShortcodeInterface $shortcode)
    {
        return $this->fragmentHandler->render(
            new ControllerReference($this->controllerName, $shortcode->getParameters()),
            $this->renderer
        );
    }
}
