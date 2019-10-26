<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Persistence\ObjectManager;
use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction;
use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistry;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepActionConfigurator;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Check that every action class extends the right action interface.
 * And for each step-tagged service, if the class extends the provided abstract class,
 *  calls some useful methods.
 */
class StepsPass implements CompilerPassInterface
{
    public const PARAMETERS_MANAGERS = 'pierstoval_character_manager.managers';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->validateManagers($container);
        $this->processConfiguredServices($container);
    }

    private function validateManagers(ContainerBuilder $container): void
    {
        $managers = $container->getParameter(static::PARAMETERS_MANAGERS);

        foreach ($managers as $name => $config) {
            $config['name'] = $name;
            $managers[$name] = $this->validateManagerSteps($config, $container);
        }

        $container->setParameter(static::PARAMETERS_MANAGERS, $managers);
    }

    /**
     * Validate steps defined in configuration, and makes sure all "action" classes are instances of StepActionInterface
     */
    private function validateManagerSteps(array $managerConfiguration, ContainerBuilder $container): array
    {
        /** @var array[] $steps */
        $steps = $managerConfiguration['steps'];

        $stepNumber = 1;

        // Validate steps that can't be validated in Configuration.
        // First loop mandatory because we re-validate each step dependency in another loop after normalization.
        foreach ($steps as $id => $step) {
            $name = $step['name'] = $id;

            if (!$step['label']) {
                $step['label'] = $this->generateStepLabel($step['name']);
            }

            $step['manager_name'] = $managerConfiguration['name'];

            $step['number'] = $stepNumber++;

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
            foreach ($step['dependencies'] as $stepDependency) {
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
                    StepActionInterface::class, class_exists($class) ? $class : \gettype($class)
                ));
            }
        }

        // And update all steps.
        $managerConfiguration['steps'] = $steps;

        return $managerConfiguration;
    }

    /**
     * Automatically convert the actions into services.
     * If they're defined as classes, this has the advantage to autowire them, etc.
     *
     * @param ContainerBuilder $container
     */
    private function processConfiguredServices(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ActionsRegistry::class)) {
            throw new InvalidConfigurationException('Step actions registry not set in your configuration. Maybe the extension was not processed properly?');
        }

        $registryDefinition = $container->getDefinition(ActionsRegistry::class);

        foreach ($container->getParameter(static::PARAMETERS_MANAGERS) as $managerName => $config) {
            $this->processStepAction($managerName, $config, $registryDefinition, $container);
        }
    }

    private function processStepAction(string $managerName, array $config, Definition $registryDefinition, ContainerBuilder $container): void
    {
        /** @var array[] $finalSteps */
        $finalSteps = $config['steps'];

        foreach ($finalSteps as $step) {
            $action = $step['action'];
            if ($container->has($action)) {
                /** @var  $definition */
                $definition = $container->getDefinition($action);
            } else {
                // If action is not yet a service, it means it's a class name.
                // In this case, we create a new service.
                $definition = new Definition($action);
                $definition
                    ->setPrivate(true)
                    ->setAutowired(true)
                ;
                $container->setDefinition($action, $definition);
            }

            $definition
                ->addMethodCall('configure', [
                    $managerName,
                    $step['name'],
                    $config['character_class'],
                    new Reference(StepResolverInterface::class),
                ])
            ;

            // If class extends the abstract one, we inject some cool services.
            if (is_a($definition->getClass(), AbstractStepAction::class, true)) {
                if ($container->hasDefinition(ObjectManager::class)) {
                    $definition->addMethodCall('setObjectManager', [new Reference(ObjectManager::class)]);
                }
                if ($container->hasDefinition(Environment::class)) {
                    $definition->addMethodCall('setTwig', [new Reference(Environment::class)]);
                }
                if ($container->hasDefinition(RouterInterface::class)) {
                    $definition->addMethodCall('setRouter', [new Reference(RouterInterface::class)]);
                }
                if ($container->hasDefinition(TranslatorInterface::class)) {
                    $definition->addMethodCall('setTranslator', [new Reference(TranslatorInterface::class)]);
                }
            }

            // Finally add the step action to the registry
            $registryDefinition->addMethodCall('addStepAction', [$managerName, $step['name'], new ServiceClosureArgument(new Reference($action))]);
        }
    }

    private function generateStepLabel(string $name): string
    {
        return Inflector::ucwords(trim(str_replace(['.', '_', '-'], ' ', $name)));
    }
}
