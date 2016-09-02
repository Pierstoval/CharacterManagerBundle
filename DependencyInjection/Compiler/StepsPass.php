<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler;

use Doctrine\Common\Inflector\Inflector;
use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction;
use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Check that every action class extends the right action interface.
 * And for each step-tagged service, if the class extends the provided abstract class,
 *  calls some useful methods.
 */
class StepsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->validateSteps($container);
        $this->processTaggedServices($container);
    }

    /**
     * Validate steps defined in configuration.
     *
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function validateSteps(ContainerBuilder $container)
    {
        $steps = $container->getParameter('pierstoval_character_manager.steps');

        $stepNumber = 1;

        // Validate steps that can't be validated in Configuration.
        // First loop mandatory because we re-validate each step dependency in another loop after normalization.
        foreach ($steps as $id => $step) {
            if (is_numeric($id)) {
                throw new InvalidConfigurationException('Step key should not be numeric but contain the step name. Maybe the extension was not processed properly?');
            } else {
                $name = $step['name'] = $id;
            }

            if (!$step['label']) {
                $step['label'] = $this->generateStepLabel($step['name']);
            }

            $step['step'] = $stepNumber++;

            $steps[$name] = $step;
        }

        foreach ($steps as $name => $step) {
            // Validate steps to disable on change, to be sure each step is defined.
            foreach ($step['onchange_clear'] as $stepToDisable) {
                if (!array_key_exists($stepToDisable, $steps)) {
                    throw new InvalidConfigurationException(sprintf(
                        'Step to disable must be a valid step name, "%s" given.'."\n".
                        'Available steps: %s',
                        $stepToDisable, implode(', ', array_keys($steps))
                    ));
                }
            }

            // Validate steps dependencies, to be sure each step is defined.
            foreach ($step['depends_on'] as $stepDependency) {
                if (!array_key_exists($stepDependency, $steps)) {
                    throw new InvalidConfigurationException(sprintf(
                        'Step dependency must be a valid step name, "%s" given.'."\n".
                        'Available steps: %s',
                        $stepDependency, implode(', ', array_keys($steps))
                    ));
                }
            }

            // Validate step actions.
            $action = $step['action'];

            // Check if action defined as a service or as a simple class.
            $class = $container->has($action) ? $container->getDefinition($action)->getClass() : $action;

            if (!class_exists($class) || !is_a($class, StepActionInterface::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Step action must be a valid class implementing %s. "%s" given.',
                    StepActionInterface::class, class_exists($class) ? $class : gettype($class)
                ));
            }
        }

        // And update all steps.
        $container->setParameter('pierstoval_character_manager.steps', $steps);

        return $steps;
    }

    /**
     * Update tagged services with cool stuff.
     *
     * @param ContainerBuilder $container
     */
    private function processTaggedServices(ContainerBuilder $container)
    {
        $definitions = $container->findTaggedServiceIds('pierstoval_character_step');

        foreach ($definitions as $serviceId => $params) {
            $definition = $container->getDefinition($serviceId);

            $class = $definition->getClass();

            // If class extends the abstract one, we inject some cool services.
            if (is_a($class, StepAction::class, true)) {
                $definition->addMethodCall('setDefaultServices', [
                    new Reference('doctrine.orm.entity_manager'),
                    new Reference('templating'),
                    new Reference('router'),
                    new Reference('translator'),
                ]);
            }

            // Make sure character class is injected into service.
            $definition->addMethodCall('setCharacterClass', [$container->getParameter('pierstoval_character_manager.character_class')]);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function generateStepLabel($name)
    {
        $name = str_replace(['.', '_', '-'], ' ', $name);
        $name = trim($name);
        $name = Inflector::ucwords($name);

        return $name;
    }
}
