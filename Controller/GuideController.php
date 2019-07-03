<?php

namespace Webfactory\ShortcodeBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Guide for the configured shortcodes showing a list overview and detail pages with the rendered shortcode.
 */
final class GuideController
{
    /**
     * @var array
     *
     * Example structure: [
     *     [
     *         'shortcode' => 'img'
     *         'example' (optional key) => '[img src="https://upload.wikimedia.org/wikipedia/en/f/f7/RickRoll.png"]'
     *         'description' (optional key) => 'Embeds the imgage located at {src}.'
     *     ]
     * ]
     */
    private $shortcodeTags;

    public function __construct(array $shortcodeTags)
    {
        $this->shortcodeTags = $shortcodeTags;
    }

    /**
     * @Template()
     *
     * @return array
     */
    public function listAction()
    {
        return [
            'shortcodeTags' => $this->shortcodeTags,
        ];
    }

    /**
     * @Template()
     *
     * @return array
     */
    public function detailAction($shortcode, Request $request)
    {
        foreach ($this->shortcodeTags as $shortcodeTag) {
            if ($shortcodeTag['shortcode'] === $shortcode) {
                // if custom parameters are provided, replace the example
                $customParameters = $request->get('customParameters');
                if ($customParameters) {
                    $shortcodeTag['example'] = $shortcode.' '.$customParameters;
                }

                return [
                    'shortcodeTag' => $shortcodeTag,
                ];
            }
        }

        throw new NotFoundHttpException();
    }
}
