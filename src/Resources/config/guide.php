<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
