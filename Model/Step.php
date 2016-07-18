<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Model;

class Step
{
    /**
     * @var int
     */
    protected $step;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var array
     */
    protected $onchangeClear;

    /**
     * @var array
     */
    protected $dependsOn;

    /**
     * @param int    $step
     * @param string $name
     * @param string $action
     * @param string $label
     * @param array  $onchangeClear
     * @param array  $dependsOn
     */
    public function __construct($step, $name, $action, $label, array $onchangeClear, array $dependsOn)
    {
        $this->step          = $step;
        $this->name          = $name;
        $this->action        = $action;
        $this->label         = $label;
        $this->onchangeClear = $onchangeClear;
        $this->dependsOn     = $dependsOn;
    }

    /**
     * @param array $data
     *
     * @return Step
     */
    public static function createFromData(array $data)
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

    /**
     * @return int
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return array
     */
    public function getOnchangeClear()
    {
        return $this->onchangeClear;
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->dependsOn;
    }
}
