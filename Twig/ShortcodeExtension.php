<?php

namespace Webfactory\ShortcodeBundle\Twig;

use Thunder\Shortcode\EventContainer\EventContainer;
use Thunder\Shortcode\Processor\Processor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig extension activating the |shortcodes filter.
 */
final class ShortcodeExtension extends AbstractExtension
{
    /** @var Processor */
    private $processor;

    public function __construct(Processor $processor)
    {
        $this->processor = $processor;
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
        return $this->processor->process($content);
    }
}
