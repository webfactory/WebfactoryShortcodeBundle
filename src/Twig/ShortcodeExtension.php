<?php

namespace Webfactory\ShortcodeBundle\Twig;

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

    public function getFilters(): array
    {
        return [
            new TwigFilter('shortcodes', [$this, 'processShortcodes'], ['is_safe' => ['html']]),
        ];
    }

    public function processShortcodes(string $content): string
    {
        return $this->processor->process($content);
    }
}
