<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

abstract class StepAction implements StepActionInterface
{
    private $class;

    /**
     * {@inheritdoc}
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

}
