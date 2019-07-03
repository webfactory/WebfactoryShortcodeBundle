<?php

namespace Webfactory\ShortcodeBundle\Twig;

use Thunder\Shortcode\ShortcodeFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension activating the |shortcodes filter.
 */
final class ShortcodeExtension extends AbstractExtension
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
            new TwigFilter('shortcodes', [$this, 'processShortcodes'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function processShortcodes($content)
    {
        return $this->facade->process($content);
    }
}
