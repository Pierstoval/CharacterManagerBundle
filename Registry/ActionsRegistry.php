<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Registry;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;

class ActionsRegistry
{
    /**
     * @var StepActionInterface[]
     */
    private $actions = [];

    public function addStepAction(StepActionInterface $action)
    {
        $this->actions[$action->getStep()->getName()] = $action;
    }

    /**
     * @return StepActionInterface[]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param string $stepName
     *
     * @return StepActionInterface
     */
    public function getAction($stepName)
    {
        if (!array_key_exists($stepName, $this->actions)) {
            throw new \InvalidArgumentException('Step "'.$stepName.'" not found in registry.');
        }

        return $this->actions[$stepName];
    }
}
