<?php

namespace Webfactory\ShortcodeBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ShortcodeTestCase extends KernelTestCase
{
    static protected $shortcodesToRegister = [];

    protected function setUp()
    {
        parent::setUp();
        static::$shortcodesToRegister = [];
    }

    protected function tearDown()
    {
        parent::tearDown();
        static::$shortcodesToRegister = [];
    }

    protected static function createKernel(array $options = array())
    {
        $kernel = new TestKernel('test', true);
        $kernel->setShortcodesToRegister(static::$shortcodesToRegister);

        return $kernel;
    }

    /**
     * @param string $templateCode
     * @param array $context
     * @return string
     */
    protected function renderTwigTemplate($templateCode, array $context)
    {
        /** @var $container ContainerInterface */
        $container = static::$kernel->getContainer();

        /** @var \Twig_Environment $twig */
        $twig = $container->get('twig');
        $template = $twig->createTemplate($templateCode);

        return $template->render($context);
    }
}
