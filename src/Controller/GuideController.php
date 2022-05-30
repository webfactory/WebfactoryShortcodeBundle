<?php

namespace Webfactory\ShortcodeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig_Environment;

/**
 * Guide for the configured shortcodes showing a list overview and detail pages with the rendered shortcode.
 */
final class GuideController
{
    private $twig;

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

    /**
     * @param Twig_Environment|Environment $twig
     */
    public function __construct(array $shortcodeTags, $twig)
    {
        $this->shortcodeTags = $shortcodeTags;
        $this->twig = $twig;
    }

    public function listAction(): Response
    {
        return new Response($this->twig->render('@WebfactoryShortcode/Guide/list.html.twig', ['shortcodeTags' => $this->shortcodeTags]));
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

                return new Response($this->twig->render('@WebfactoryShortcode/Guide/detail.html.twig', ['shortcodeTag' => $shortcodeTag]));
            }
        }

        throw new NotFoundHttpException();
    }
}
