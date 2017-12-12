<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('managers')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(true)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('character_class')->isRequired()->end()
                            ->arrayNode('steps')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('action')
                                            ->info('Can be a class or a service. Must implement StepActionInterface or extend abstract Action class.')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('label')->defaultValue('')->end()
                                        ->arrayNode('dependencies')
                                            ->info('Steps that the current step may depend on. If step is not set in session, will throw an exception.')
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->arrayNode('onchange_clear')
                                            ->info("When this step will be updated, it will clear values for specified steps.\nOnly available for the abstract class")
                                            ->defaultValue([])
                                            ->prototype('scalar')->end()
                                        ->end()
                                    ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
