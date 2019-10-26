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

interface ActionsRegistryInterface
{
    /**
     * @param StepActionInterface|\Closure $action If it's a callable, it's lazy-loaded.
     */
    public function addStepAction(string $manager, string $stepName, $action): void;

    /**
     * @throws \RuntimeException if there are no managers available.
     * @throws \InvalidArgumentException for an action that do not exist.
     * @throws \InvalidArgumentException for a manager that do not exist.
     */
    public function getAction(string $stepName, string $manager = null): StepActionInterface;
}
