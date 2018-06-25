<?php

namespace Webfactory\ShortcodeBundle\Twig;

use Thunder\Shortcode\ShortcodeFacade;

/**
 * Twig extension activating the |shortcodes filter.
 */
final class ShortcodeExtension extends \Twig_Extension
{
    /** @var ShortcodeFacade */
    private $facade;

    /**
     * @param ShortcodeFacade $facade
     */
    public function __construct(ShortcodeFacade $facade)
    {
        $this->facade = $facade;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('shortcodes', [$this, 'processShortcodes'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $content
     * @return string
     */
    public function processShortcodes($content)
    {
        return $this->facade->process($content);
    }
}
