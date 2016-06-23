<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Character;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
                ->scalarNode('character_class')
                    ->isRequired()
                    ->validate()
                        ->always()
                        ->then(function($value){
                            if (!class_exists($value) || !is_a($value, Character::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Character class must be a valid class extending %s. "%s" given.',
                                    Character::class, $value
                                ));
                            }
                        })
                    ->end()
                ->end()
                ->arrayNode('steps')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('action')->isRequired()->end()
                            ->scalarNode('name')->defaultValue('')->end()
                            ->scalarNode('label')->defaultValue('')->end()
                            ->arrayNode('steps_to_disable_on_change')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
