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
    protected $stepsToDisableOnChange;

    /**
     * @param int    $step
     * @param string $name
     * @param string $action
     * @param string $label
     * @param array  $stepsToDisableOnChange
     */
    public function __construct($step, $name, $action, $label, array $stepsToDisableOnChange)
    {
        $this->step                   = $step;
        $this->name                   = $name;
        $this->action                 = $action;
        $this->label                  = $label;
        $this->stepsToDisableOnChange = $stepsToDisableOnChange;
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
    public function getStepsToDisableOnChange()
    {
        return $this->stepsToDisableOnChange;
    }

}
