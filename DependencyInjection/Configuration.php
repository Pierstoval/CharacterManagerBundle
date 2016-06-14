<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('pierstoval_character_manager');

        $rootNode
            ->children()
                ->arrayNode('steps')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('action')->isRequired()->end()
                            ->scalarNode('name')->defaultValue('')->end()
                            ->scalarNode('label')->defaultValue('')->end()
                            ->arrayNode('steps_to_disable_on_change')->end()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
