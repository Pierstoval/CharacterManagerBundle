<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Model;

class Step
{
    protected $step;
    protected $name;
    protected $action;
    protected $label;
    protected $onchangeClear;
    protected $dependsOn;

    /**
     * @param int    $step
     * @param string $name
     * @param string $action
     * @param string $label
     * @param array  $onchangeClear
     * @param array  $dependsOn
     */
    public function __construct(int $step, string $name, string $action, $label, array $onchangeClear, array $dependsOn)
    {
        $this->step          = $step;
        $this->name          = $name;
        $this->action        = $action;
        $this->label         = $label;
        $this->onchangeClear = $onchangeClear;
        $this->dependsOn     = $dependsOn;
    }

    public static function createFromData(array $data): Step
    {
        return new static(
            $data['step'],
            $data['name'],
            $data['action'],
            $data['label'],
            $data['onchange_clear'],
            $data['depends_on']
        );
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getOnchangeClear(): array
    {
        return $this->onchangeClear;
    }

    public function getDependencies(): array
    {
        return $this->dependsOn;
    }
}
