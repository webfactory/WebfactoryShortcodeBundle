<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->private();

    $services->set(\Thunder\Shortcode\HandlerContainer\HandlerContainer::class);

    $services->set('webfactory_shortcode.regular_parser', \Thunder\Shortcode\Parser\RegularParser::class);

    $services->set('webfactory_shortcode.regex_parser', \Thunder\Shortcode\Parser\RegexParser::class);

    $services->set(\Webfactory\ShortcodeBundle\Factory\ProcessorFactory::class)
        ->args([
            service('webfactory_shortcode.parser'),
            service(\Thunder\Shortcode\HandlerContainer\HandlerContainer::class),
            service(\Thunder\Shortcode\EventContainer\EventContainer::class),
            '%webfactory_shortcode.recursion_depth%',
            '%webfactory_shortcode.max_iterations%',
        ]);

    $services->set(\Thunder\Shortcode\Processor\Processor::class)
        ->public()
        ->factory([service(\Webfactory\ShortcodeBundle\Factory\ProcessorFactory::class), 'create']);

    $services->set(\Thunder\Shortcode\EventContainer\EventContainer::class)
        ->call('addListener', [
            \Thunder\Shortcode\Events::REPLACE_SHORTCODES,
            inline_service(\Webfactory\ShortcodeBundle\Handler\RemoveWrappingParagraphElementsEventHandler::class),
        ]);

    $services->set('Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.esi', \Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler::class)
        ->abstract()
        ->lazy()
        ->args([
            service('fragment.handler'),
            '',
            'esi',
            service('request_stack'),
            service('logger')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => 'shortcode']);

    $services->alias('webfactory.shortcode.embed_esi_for_shortcode_handler', 'Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.esi');

    $services->set('Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline', \Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler::class)
        ->abstract()
        ->lazy()
        ->args([
            service('fragment.handler'),
            '',
            'inline',
            service('request_stack'),
            service('logger')->nullOnInvalid(),
        ])
        ->tag('monolog.logger', ['channel' => 'shortcode']);

    $services->alias('webfactory.shortcode.embed_inline_for_shortcode_handler', 'Webfactory\ShortcodeBundle\Handler\EmbeddedShortcodeHandler.inline');

    $services->set(\Webfactory\ShortcodeBundle\Twig\ShortcodeExtension::class, \Webfactory\ShortcodeBundle\Twig\ShortcodeExtension::class)
        ->args([service(\Thunder\Shortcode\Processor\Processor::class)])
        ->tag('twig.extension');

    $services->set(\Webfactory\ShortcodeBundle\Test\ShortcodeDefinitionTestHelper::class)
        ->public()
        ->args([
            service('controller_resolver'),
            service(\Thunder\Shortcode\HandlerContainer\HandlerContainer::class),
        ]);
};
