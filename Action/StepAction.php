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
    /**
     * Used when you need to change the translation domain used in controller-generated messages.
     */
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

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setEntityManager(EntityManager $em): void
    {
        $this->em = $em;
    }

    public function setTemplating(TwigEngine $templating): void
    {
        $this->templating = $templating;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function setCharacterClass(string $class): void
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
    public function getCharacterClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function setStep(Step $step): void
    {
        $this->step = $step;
    }

    /**
     * {@inheritdoc}
     */
    public function getStep(): Step
    {
        return $this->step;
    }

    /**
     * {@inheritdoc}
     */
    public function setSteps(array $steps): void
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
    public function getSteps(): array
    {
        return $this->steps;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCharacter()
    {
        return $this->getSession()->get('character', []) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCharacterProperty(string $key = null)
    {
        if (null === $key) {
            if (!$this->step instanceof Step) {
                throw new \InvalidArgumentException(sprintf(
                    'To get current step you need to use %s:%s method and inject a Step instance.',
                    __CLASS__, 'setStep'
                ));
            }
            $key = $this->step->getName();
        }

        $character = $this->getCurrentCharacter();

        return $character[$key] ?? null;
    }

    /**
     * @return RedirectResponse
     */
    protected function nextStep(): RedirectResponse
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
    protected function goToStep(int $stepNumber): RedirectResponse
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
    protected function updateCharacterStep($value): void
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
     */
    protected function flashMessage(string $msg, string $type = null, array $msgParams = []): self
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

        $session = $this->getSession();

        if (!$session instanceof Session) {
            throw new \RuntimeException('Current session must be an instance of '.Session::class.' to use the FlashBag');
        }

        $flashbag = $session->getFlashBag();

        // Add the message manually.
        $existingMessages = $flashbag->peek($type);
        $existingMessages[] = $msg;

        // And avoid having the same message multiple times.
        $flashbag->set($type, array_unique($existingMessages));

        return $this;
    }

    protected function getRequest(): Request
    {
        if (!$this->request) {
            throw new \InvalidArgumentException('Request is not set in step action.');
        }

        return $this->request;
    }

    protected function getSession(): SessionInterface
    {
        if (!$session = $this->getRequest()->getSession()) {
            throw new \InvalidArgumentException('No session available in current request.');
        }

        return $session;
    }
}
