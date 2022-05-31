<?php

namespace Webfactory\ShortcodeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('webfactory_shortcode');
        $rootNode = method_exists(TreeBuilder::class, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('webfactory_shortcode');

        // For details on these configuration options, see https://github.com/thunderer/Shortcode#parsing and
        // https://github.com/thunderer/Shortcode#configuration .
        $rootNode
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
                ->arrayNode('shortcodes')
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()->then(static function ($v) {
                                return ['controller' => $v];
                            })
                            ->end()
                        ->children()
                            ->scalarNode('controller')->isRequired()->end()
                            ->enumNode('method')->values(['esi', 'inline'])->defaultValue('inline')->end()
                            ->scalarNode('description')->defaultNull()->end()
                            ->scalarNode('example')->defaultNull()->end();

        return $treeBuilder;
    }
}
