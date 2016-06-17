<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PierstovalCharacterGeneratorExtension extends Extension
{

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $stepNumber = 1;

        $finalSteps = [];

        // Validate steps that can't be validated in Configuration
        foreach ($config['steps'] as $name => $step) {
            if (is_numeric($name)) {
                $name = $step['name'];
            } else {
                $step['name'] = $name;
            }

            if (!$step['label']) {
                $step['label'] = Inflector::ucwords(str_replace('_', ' ', $name));
            }

            $step['step'] = $stepNumber++;

            // Cannot use "defaultValue" for arrayNode in prototypes, so using this...
            if (!array_key_exists('steps_to_disable_on_change', $step)) {
                $step['steps_to_disable_on_change'] = [];
            }

            $finalSteps[$name] = $step;
        }

        // Validate steps to disable after normalization, to be sure each step is defined.
        foreach ($finalSteps as $name => $step) {
            foreach ($step['steps_to_disable_on_change'] as $stepToDisable) {
                if (!array_key_exists($stepToDisable, $finalSteps)) {
                    throw new InvalidConfigurationException(sprintf(
                        'Step to disable must be a valid step name, "%s" given.'."\n".
                        'Available steps: %s',
                        $stepToDisable, implode(', ', array_keys($finalSteps))
                    ));
                }
            }
        }

        $config['steps'] = $finalSteps;

        foreach ($config as $key => $value) {
            $container->set('pierstoval_character_manager')
        }
    }
}
