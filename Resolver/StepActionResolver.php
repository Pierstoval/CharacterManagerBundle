<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Resolver;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;

class StepActionResolver
{
    /**
     * @var array
     */
    private $stepsConfiguration;

    /**
     * @var Step[]
     */
    private $steps;

    public function __construct(array $stepsConfiguration = [])
    {
        $this->stepsConfiguration = $stepsConfiguration;
    }

    /**
     * @return Step[]
     */
    public function getSteps()
    {
        if (null === $this->steps) {
            $this->resolveAllSteps();
        }

        return $this->steps ?: [];
    }

    /**
     * @param string $stepName
     *
     * @return Step
     */
    public function resolve($stepName)
    {
        if (null === $this->steps) {
            $this->resolveAllSteps();
        }

        if (!array_key_exists($stepName, $this->steps)) {
            return null;
        }

        return $this->steps[$stepName];
    }

    /**
     * Create Step instances from steps configuration.
     */
    private function resolveAllSteps()
    {
        $steps = [];

        // Transform array of array in array of value objects.
        foreach ($this->stepsConfiguration as $key => $stepArray) {
            $steps[$key] = Step::createFromData($stepArray);
        }

        if (count($steps)) {
            $this->steps = $steps;
        } else {
            throw new \RuntimeException('No steps in resolver');
        }
    }
}
