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

class Step implements StepInterface
{
    protected $number;
    protected $name;
    protected $action;
    protected $label;
    protected $onchangeClear;
    protected $dependencies;
    protected $managerName;

    public function __construct(
        int $number,
        string $name,
        string $label,
        string $action,
        string $managerName,
        array $onchangeClear,
        array $dependencies
    ) {
        $this->number        = $number;
        $this->name          = $name;
        $this->action        = $action;
        $this->label         = $label;
        $this->managerName   = $managerName;
        $this->onchangeClear = $onchangeClear;
        $this->dependencies  = $dependencies;
    }

    public static function createFromData(array $data): Step
    {
        return new static(
            $data['number'],
            $data['name'],
            $data['label'],
            $data['action'],
            $data['manager_name'],
            $data['onchange_clear'],
            $data['dependencies']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getOnchangeClear(): array
    {
        return $this->onchangeClear;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerName(): string
    {
        return $this->managerName;
    }
}
