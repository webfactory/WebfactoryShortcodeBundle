<?php

namespace Webfactory\ShortcodeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Guide for the configured shortcodes showing a list overview and detail pages with the rendered shortcode.
 */
class GuideController extends AbstractController
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

    public function listAction(): Response
    {
        return $this->render('@WebfactoryShortcode/Guide/list.html.twig', [
            'shortcodeTags' => $this->shortcodeTags,
        ]);
    }

    public function detailAction($shortcode, Request $request): Response
    {
        foreach ($this->shortcodeTags as $shortcodeTag) {
            if ($shortcodeTag['shortcode'] === $shortcode) {
                // if custom parameters are provided, replace the example
                $customParameters = $request->get('customParameters');
                if ($customParameters) {
                    $shortcodeTag['example'] = $shortcode.' '.$customParameters;
                }

                return $this->render('@WebfactoryShortcode/Guide/detail.html.twig', [
                    'shortcodeTag' => $shortcodeTag,
                ]);
            }
        }

        throw new NotFoundHttpException();
    }
}
