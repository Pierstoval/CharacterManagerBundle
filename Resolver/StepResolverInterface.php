<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Resolver;

use Pierstoval\Bundle\CharacterManagerBundle\Exception\StepNotFoundException;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;

/**
 * If no manager is provided in the resolve methods, it will behave differently based on the numbers of managers.
 * If there is only one manager, $managerName will default to it.
 * Else, if there is more than one manager, an exception is thrown, explaining the user to specify a manager name.
 */
interface StepResolverInterface
{
    /**
     * Resolves a step object based on a step identifier.
     * @throws StepNotFoundException if step does not exist
     */
    public function resolve(string $stepName, string $managerName = null): StepInterface;

    /**
     * Resolves a step object based on a step number.
     * @throws StepNotFoundException if step number does not exist
     */
    public function resolveNumber(int $stepNumber, string $managerName = null): StepInterface;

    /**
     * Resolves all steps for a manager name and return them as array.
     *
     * @return StepInterface[]
     */
    public function getManagerSteps(string $managerName = null): array;

    /**
     * Throws an exception if $managerName is null and there are multiple managers configured.
     * If one manager is configured and $managerName is null, it will return the only available manager name.
     */
    public function resolveManagerName(string $managerName = null): string;
}
