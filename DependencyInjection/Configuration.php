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
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('character_class')
                    ->isRequired()
                    ->validate()
                        ->always()
                        ->then(function ($value) {
                            if (!class_exists($value) || !is_a($value, Character::class, true)) {
                                throw new InvalidConfigurationException(sprintf(
                                    'Character class must be a valid class extending %s. "%s" given.',
                                    Character::class, $value
                                ));
                            }
                            return $value;
                        })
                    ->end()
                ->end()
                ->arrayNode('steps')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('action')
                                ->info('Can be a class or a service. Must implement interface or extend abstract classes from this bundle.')
                                ->isRequired()
                            ->end()
                            ->scalarNode('name')->defaultValue('')->end()
                            ->scalarNode('label')->defaultValue('')->end()
                            ->arrayNode('depends_on')
                                ->info('Steps that the current step may depend on. If step is not set in session, will throw an exception.')
                                ->defaultValue([])
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('onchange_clear')
                                ->info('When this step will be updated, it will clear values for specified steps.')
                                ->defaultValue([])
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
