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
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;

class StepResolver implements StepResolverInterface
{
    /**
     * @var array[]
     */
    private $managersConfiguration;

    /**
     * @var StepInterface[][]
     */
    private $steps;

    public function __construct(array $managersConfiguration = [])
    {
        $this->managersConfiguration = $managersConfiguration;
    }

    public function resolve(string $stepName, string $managerName = null): StepInterface
    {
        $this->resolveManagerSteps($managerName);

        $managerName = $this->resolveManagerName($managerName);

        if (!isset($this->steps[$managerName][$stepName])) {
            throw new StepNotFoundException($stepName, $managerName);
        }

        return $this->steps[$managerName][$stepName];
    }

    public function getManagerSteps(string $managerName = null): array
    {
        $this->resolveManagerSteps($managerName);

        return $this->steps[$managerName];
    }

    public function resolveNumber(int $stepNumber, string $managerName = null): StepInterface
    {
        $managerName = $this->resolveManagerName($managerName);

        $this->resolveManagerSteps($managerName);

        foreach ($this->steps[$managerName] as $step) {
            if ($step->getNumber() === $stepNumber) {
                return $step;
            }
        }

        throw new StepNotFoundException($stepNumber, $managerName);
    }

    private function resolveManagerSteps(string $managerName = null): void
    {
        $managerName = $this->resolveManagerName($managerName);

        if (isset($this->steps[$managerName])) {
            return;
        }

        // Transform array of array in array of value objects.
        foreach ($this->managersConfiguration as $manager => $config) {
            if ($manager !== $managerName) {
                continue;
            }
            $this->steps[$manager] = [];
            foreach ($config['steps'] as $key => $stepArray) {
                $this->steps[$manager][$key] = Step::createFromData($stepArray);
            }
        }
    }

    public function resolveManagerName(string $managerName = null): string
    {
        if (\count($this->managersConfiguration) === 0) {
            throw new \RuntimeException('No character managers to resolve configuration for.');
        }

        if (!$managerName && \count($this->managersConfiguration) > 0) {
            if (\count($this->managersConfiguration) === 1) {
                return array_keys($this->managersConfiguration)[0];
            }

            throw new \InvalidArgumentException(sprintf(
                'You did not specify which character manager you want to get the steps from, and you have more than one manager. '.
                'Possible choices: %s',
                implode(', ', array_keys($this->managersConfiguration))
            ));
        }

        if (!isset($this->managersConfiguration[$managerName])) {
            throw new \InvalidArgumentException("\"$managerName\" manager does not exist, or is not initialized yet.");
        }

        return $managerName;
    }
}
