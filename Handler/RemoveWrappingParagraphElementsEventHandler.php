<?php

namespace Webfactory\ShortcodeBundle\Handler;

use Thunder\Shortcode\Event\ReplaceShortcodesEvent;
use Thunder\Shortcode\Shortcode\ReplacedShortcode;

/**
 * Removes <p>...</p> tags if they directly wrap a short code.
 *
 * @see https://github.com/thunderer/Shortcode/issues/40
 */
final class RemoveWrappingParagraphElementsEventHandler
{
    /**
     * @param ReplaceShortcodesEvent $event
     */
    public function __invoke(ReplaceShortcodesEvent $event)
    {
        /* The text still containing shortcodes */
        $text = $event->getText();

        /* The generated ReplacedShortcode instances. Each ReplacedShortcode contains the Shortcode string in the
          original form, it's offset and it's replacement string. */
        $replacements = $event->getReplacements();
        if (!$replacements) {
            return;
        }

        /* Do the replacements from end to back to front, so the offsets remain valid. */
        $replacements_reversed = array_reverse($replacements);
        $result = array_reduce(
            $replacements_reversed,
            function ($state, ReplacedShortcode $r) {
                $offset = $r->getOffset();
                $lengthOfShortcode = mb_strlen($r->getText(), 'utf-8');
                $sourceTextBeforeShortcode = mb_substr($state, 0, $offset, 'utf-8');
                $sourceTextAfterShortcode = mb_substr($state, $offset + $lengthOfShortcode, null, 'utf-8');

                if (
                    preg_match('~(\s*<p>\s*)$~', $sourceTextBeforeShortcode, $prefixMatch)
                    && preg_match('~(^\s*</p>\s*)~', $sourceTextAfterShortcode, $postfixMatch)
                ) {
                    $sourceTextBeforeShortcode = mb_substr($state, 0, $offset - mb_strlen($prefixMatch[0], 'utf-8'), 'utf-8');
                    $sourceTextAfterShortcode = mb_substr(
                        $state,
                        $offset + $lengthOfShortcode + mb_strlen($postfixMatch[0], 'utf-8'),
                        null,
                        'utf-8'
                    );
                }

                return $sourceTextBeforeShortcode.$r->getReplacement().$sourceTextAfterShortcode;
            },
            $text
        );

        $event->setResult($result);
    }
}
