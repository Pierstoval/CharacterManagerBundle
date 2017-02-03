<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Doctrine\ORM\EntityManager;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param EntityManager       $em
     * @param TwigEngine          $templating
     * @param RouterInterface     $router
     * @param TranslatorInterface $translator
     */
    public function setDefaultServices(EntityManager $em, TwigEngine $templating, RouterInterface $router, TranslatorInterface $translator)
    {
        $this->em         = $em;
        $this->templating = $templating;
        $this->router     = $router;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function setCharacterClass($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getCharacterClass()
    {
        return $this->class;
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
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCharacter()
    {
        if (!$this->request) {
            throw new \InvalidArgumentException('Request is not set in step action.');
        }

        return $this->request->getSession()->get('character');
    }

    /**
     * {@inheritdoc}
     */
    public function getCharacterProperty($key = null)
    {
        if (null === $key) {
            $key = $this->step->getName();
        }

        $character = $this->request->getSession()->get('character', []);

        return array_key_exists($key, $character) ? $character[$key] : null;
    }

    /**
     * @return RedirectResponse
     */
    protected function nextStep()
    {
        return $this->goToStep($this->step->getStep() + 1);
    }

    /**
     * Redirects to a specific step and updates the session.
     *
     * @param int $stepNumber
     *
     * @return RedirectResponse
     */
    protected function goToStep($stepNumber)
    {
        if (!$this->request) {
            throw new \InvalidArgumentException('Request is not set in step action.');
        }

        foreach ($this->steps as $step) {
            if ($step->getStep() === $stepNumber) {
                $this->request->getSession()->set('step', $stepNumber);

                return new RedirectResponse($this->router->generate('pierstoval_character_generator_step', ['requestStep' => $step->getName()]));
            }
        }

        throw new \InvalidArgumentException('Invalid step: '.$stepNumber);
    }

    /**
     * @param mixed $value
     */
    protected function updateCharacterStep($value)
    {
        if (!$this->request) {
            throw new \InvalidArgumentException('Request is not set in step action.');
        }

        $character = $this->request->getSession()->get('character', []);

        $character[$this->step->getName()] = $value;

        foreach ($this->step->getOnchangeClear() as $stepToDisable) {
            unset($character[$stepToDisable]);
        }

        $this->request->getSession()->set('step', $this->step->getStep());
        $this->request->getSession()->set('character', $character);
    }

    /**
     * Adds a new flash message.
     *
     * @param string $msg
     * @param string $type
     * @param array  $msgParams
     *
     * @return $this
     */
    public function flashMessage($msg, $type = null, array $msgParams = [])
    {
        // Allows not knowing about the default type in the method signature.
        if (null === $type) {
            $type = 'error';
        }

        if (!$this->request) {
            throw new \InvalidArgumentException('Request is not set in step action.');
        }

        if (!$this->translator) {
            throw new \InvalidArgumentException('Translator is not set in step action.');
        }

        $msg = $this->translator->trans($msg, $msgParams, 'CorahnRinBundle');

        /** @var Session $session */
        $session = $this->request->getSession();

        $flashbag = $session->getFlashBag();

        // Add the message manually.
        $existingMessages = $flashbag->peek($type);
        $existingMessages[] = $msg;

        // And avoid having the same message multiple times.
        $flashbag->set($type, array_unique($existingMessages));

        return $this;
    }
}
