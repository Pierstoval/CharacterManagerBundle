<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Symfony\Component\HttpFoundation\Request;

abstract class StepAction implements StepActionInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Step
     */
    protected $step;

    /**
     * @var Step[]
     */
    protected $steps;

    /**
     * @var string
     */
    protected $stepName;

    /**
     * {@inheritdoc}
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function setStep(Step $step)
    {
        $this->step = $step;
    }

    /**
     * {@inheritdoc}
     */
    public function setSteps(array $steps)
    {
        $this->steps = $steps;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepName($stepName)
    {
        $this->stepName = $stepName;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCharacter()
    {
        return $this->request->getSession()->get('character');
    }

    /**
     * {@inheritdoc}
     */
    public function getCharacterProperty($key)
    {
        $character = $this->request->getSession()->get('character');

        return array_key_exists($key, $character) ? $character[$key] : null;
    }
}
