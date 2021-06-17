<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('webfactory_shortcode');

        $treeBuilder->getRootNode()
            ->children()
                ->enumNode('parser')
                    ->info('Which parser type to use, choose "regular" or "regex".')
                    ->values(['regular', 'regex'])
                    ->defaultValue('regular')
                ->end()
                ->integerNode('recursion_depth')
                    ->info('Controls how many levels of shortcodes to process')
                    ->defaultValue(null)
                ->end()
                ->integerNode('max_iterations')
                    ->info('Limit the number of iterations when resolving shortcodes')
                    ->defaultValue(null)
                ->end()
        ;

        return $treeBuilder;
    }
}
