<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface StepActionInterface
{
    /**
     * Any step action is like a controller action: we inject the request to it, and we need a response.
     * As we can have tons of steps, it's much better to rely on action pattern,
     *   rather than one controller with tons of methods.
     */
    public function execute(): Response;

    /**
     * Should be executed when compiling the service in the container, or before it's executed.
     */
    public function configure(string $managerName, string $stepName, string $characterClassName, StepResolverInterface $resolver): void;

    public function getStep(): StepInterface;

    public function stepName(): string;

    /**
     * Current Request object that will be used in the Step action.
     */
    public function setRequest(Request $request): void;
}
