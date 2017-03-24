<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Doctrine\ORM\EntityManager;
use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class StepAction implements StepActionInterface
{
    protected static $translationDomain = 'PierstovalCharacterBundle';

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
    protected $steps = [];

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
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param TwigEngine $templating
     */
    public function setTemplating(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function setCharacterClass($class)
    {
        if (!class_exists($class) || !is_a($class, CharacterInterface::class, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Step action must be a valid class implementing %s. "%s" given.',
                CharacterInterface::class, class_exists($class) ? $class : gettype($class)
            ));
        }

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
        foreach ($steps as $step) {
            if (!$step instanceof Step) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected %s instance, "%s" given.',
                    StepActionInterface::class, is_object($step) ? get_class($step) : gettype($step)
                ));
            }
        }

        $this->steps = $steps;
    }

    /**
     * {@inheritdoc}
     */
    public function getSteps()
    {
        return $this->steps;
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
        return $this->getSession()->get('character', []);
    }

    /**
     * {@inheritdoc}
     */
    public function getCharacterProperty($key = null)
    {
        if (null === $key) {
            if (!$this->step) {
                throw new \InvalidArgumentException(sprintf(
                    'To get current step you need to use %s:%s method and inject a Step instance.',
                    __CLASS__, 'setStep'
                ));
            }
            $key = $this->step->getName();
        }

        $character = $this->getCurrentCharacter();

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
        if (!$this->router) {
            throw new \InvalidArgumentException('Cannot use '.__METHOD__.' if no router is injected in StepAction.');
        }

        foreach ($this->steps as $step) {
            if ($step->getStep() === $stepNumber) {
                $this->getSession()->set('step', $stepNumber);

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
        $character = $this->getCurrentCharacter();

        $character[$this->step->getName()] = $value;

        foreach ($this->step->getOnchangeClear() as $stepToDisable) {
            unset($character[$stepToDisable]);
        }

        $this->getSession()->set('step', $this->step->getStep());
        $this->getSession()->set('character', $character);
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
    protected function flashMessage($msg, $type = null, array $msgParams = [])
    {
        // Allows not knowing about the default type in the method signature.
        if (null === $type) {
            $type = 'error';
        }

        if ($this->translator) {
            $msg = $this->translator->trans($msg, $msgParams, static::$translationDomain);
        } elseif (count($msgParams)) {
            $msg = strtr($msg, $msgParams);
        }

        /** @var Session $session */
        $session = $this->getSession();

        $flashbag = $session->getFlashBag();

        // Add the message manually.
        $existingMessages = $flashbag->peek($type);
        $existingMessages[] = $msg;

        // And avoid having the same message multiple times.
        $flashbag->set($type, array_unique($existingMessages));

        return $this;
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        if (!$this->request) {
            throw new \InvalidArgumentException('Request is not set in step action.');
        }

        return $this->request;
    }

    /**
     * @return Session|SessionInterface
     */
    protected function getSession()
    {
        if (!$session = $this->getRequest()->getSession()) {
            throw new \InvalidArgumentException('No session available in current request.');
        }

        return $session;
    }
}
