<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Registry;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;

class ActionsRegistry implements ActionsRegistryInterface
{
    /**
     * @var StepActionInterface[][]
     */
    private $actions = [];

    public function addStepAction(string $manager, string $stepName, $action): void
    {
        $this->actions[$manager][$stepName] = $action;
    }

    public function getAction(string $stepName, string $manager = null): StepActionInterface
    {
        if (!$this->actions) {
            throw new \RuntimeException('No actions in the registry.');
        }

        if ($manager === null) {
            $manager = array_keys($this->actions)[0];
        }

        if (!isset($this->actions[$manager])) {
            throw new \InvalidArgumentException(\sprintf(
                'Manager "%s" does not exist.',
                $manager
            ));
        }

        if (!isset($this->actions[$manager][$stepName])) {
                throw new \InvalidArgumentException(\sprintf(
                'Step "%s" not found in manager "%s".',
                $stepName, $manager
            ));
        }

        $action = $this->actions[$manager][$stepName];

        if ($action instanceof \Closure) {
            // Lazy loading
            $action = $this->actions[$manager][$stepName]();
            if (!$action instanceof StepActionInterface) {
                throw new \RuntimeException(\sprintf(
                    "Lazy-loaded action \"%s\" for character manager \"%s\" must be resolved to an instance of \"%s\".\n\"%s\" given.",
                    $stepName, $manager, StepActionInterface::class, \is_object($action) ? \get_class($action) : \gettype($action)
                ));
            }
            $this->actions[$manager][$stepName] = $action;
        }

        return $action;
    }
}
