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

    public function addStepAction(string $manager, StepActionInterface $action): void
    {
        $this->actions[$manager][$action->getStep()->getName()] = $action;
    }

    /**
     * @return StepActionInterface[]
     */
    public function getActions(): array
    {
        return $this->actions;
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
                'Manager %s does not exist.',
                $manager
            ));
        }

        if (!isset($this->actions[$manager][$stepName])) {
            throw new \InvalidArgumentException(\sprintf(
                'Step "%s:%s" not found in registry.',
                $manager, $stepName
            ));
        }

        return $this->actions[$manager][$stepName];
    }
}
