<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StepsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $steps = $container->getParameter('pierstoval_character_manager.steps');

        // Validate step actions
        foreach ($steps as $name => $step) {
            $action = $step['action'];

            if ($container->has($action)) {
                // Action defined as a service.
                $definition = $container->getDefinition($action);

                $class = $definition->getClass();
            } else {
                // Else, action defined as a simple class.
                $class = $action;
            }

            if (!class_exists($class) || !is_a($class, StepAction::class, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Step action must be a valid class extending %s. "%s" given.',
                    StepAction::class, class_exists($class) ? $class : gettype($class)
                ));
            }
        }
    }
}
