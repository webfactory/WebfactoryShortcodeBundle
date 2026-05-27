<?php

namespace Webfactory\ShortcodeBundle\Handler;

use Thunder\Shortcode\Event\ReplaceShortcodesEvent;

/**
 * Removes "</p>...<p>" directly inside a shortcode.
 *
 * This may happen when using shortcodes with content like the following, where the outer "<p>...</p>" will be removed
 * by `RemoveWrappingParagraphElementsEventHandler`.
 *
 * <p>[shortcode]</p><p>Inner content</p><p>[/shortcode]</p>
 */
final class RemovePendingParagraphElementsInsideShortcodeEventHandler
{
    public function __invoke(ReplaceShortcodesEvent $event): void
    {
        if (!$event->getShortcode()) {
            return;
        }

        $text = $event->getText();

        if (
            preg_match('~^\s*</p>\s*~', $text, $prefixMatch)
            && preg_match('~\s*<p>\s*$~', $text, $postfixMatch)
        ) {
            $event->setResult(mb_substr($text, mb_strlen($prefixMatch[0], 'utf-8'), -mb_strlen($postfixMatch[0], 'utf-8')));
        }
    }
}
