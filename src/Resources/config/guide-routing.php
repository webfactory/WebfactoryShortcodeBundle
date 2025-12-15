<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Webfactory\ShortcodeBundle\Controller\GuideController;

return static function (RoutingConfigurator $routes): void {
    foreach (debug_backtrace() as $trace) {
        if (isset($trace['object']) && $trace['object'] instanceof XmlFileLoader && 'doImport' === $trace['function']) {
            if (__DIR__ === dirname(realpath($trace['args'][3]))) {
                trigger_deprecation('webfactory/shortcode-bundle', '2.7', 'The "guide-routing.xml" routing configuration file is deprecated, import "guide-routing.php" instead.');

                break;
            }
        }
    }

    $routes->add('webfactory.shortcode.guide-list', '/')
        ->controller([GuideController::class, 'listAction']);

    $routes->add('webfactory.shortcode.guide-detail', '/{shortcode}/')
        ->controller([GuideController::class, 'detailAction']);
};
