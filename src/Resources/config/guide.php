<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function(ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(\Webfactory\ShortcodeBundle\Controller\GuideController::class)
        ->args([
            '',
            service('twig'),
        ])
        ->tag('controller.service_arguments');
};
