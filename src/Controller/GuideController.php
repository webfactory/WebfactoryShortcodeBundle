<?php

namespace Webfactory\ShortcodeBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormFactoryInterface;
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
     * @var Environment|Twig_Environment
     */
    private $twig;

    /**
     * @var ?FormFactoryInterface
     */
    private $formFactory;

    /**
     * @param Twig_Environment|Environment $twig
     */
    public function __construct(array $shortcodeTags, $twig, FormFactoryInterface $formFactory = null)
    {
        $this->shortcodeTags = array_combine(array_map(function (array $definition): string { return $definition['shortcode']; }, $shortcodeTags), $shortcodeTags);
        $this->twig = $twig;
        $this->formFactory = $formFactory;
    }

    public function listAction(): Response
    {
        return new Response($this->twig->render('@WebfactoryShortcode/Guide/list.html.twig', ['shortcodeTags' => $this->shortcodeTags]));
    }

    public function detailAction(string $shortcode, Request $request): Response
    {
        if (!isset($this->shortcodeTags[$shortcode])) {
            throw new NotFoundHttpException();
        }

        $shortcodeTag = $this->shortcodeTags[$shortcode];

        // if custom parameters are provided, replace the example
        $customParameters = $request->get('customParameters');
        if ($customParameters) {
            $shortcodeTag['example'] = $shortcode.' '.$customParameters;
        }

        $example = '[' . ($shortcodeTag['example'] ?? $shortcode ) . ']';

        if ($this->formFactory) {
            $formBuilder = $this->formFactory->createBuilder(FormType::class, ['example' => $example], ['method' => 'GET']);
            $formBuilder->add('example', TextareaType::class);
            $form = $formBuilder->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                $example = $form->getData()['example'];
            }
        } else {
            $form = null;
        }

        return new Response(
            $this->twig->render(
                '@WebfactoryShortcode/Guide/detail.html.twig', [
                    'shortcodeTag' => $shortcodeTag,
                    'example' => $example,
                    'form' => $form ? $form->createView() : null,
                ]
            )
        );
    }
}
