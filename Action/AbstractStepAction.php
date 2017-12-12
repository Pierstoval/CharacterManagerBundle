<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Doctrine\Common\Persistence\ObjectManager;
use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;
use Twig\Environment;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractStepAction implements StepActionInterface
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
     * @var StepInterface
     */
    protected $step;

    /**
     * @var StepInterface[]
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
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setObjectManager(ObjectManager $em): void
    {
        $this->em = $em;
    }

    public function setTwig(Environment $twig): void
    {
        $this->twig = $twig;
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
                CharacterInterface::class, class_exists($class) ? $class : \gettype($class)
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
    public function setStep(StepInterface $step): void
    {
        $this->step = $step;
    }

    /**
     * {@inheritdoc}
     */
    public function getStep(): StepInterface
    {
        return $this->step;
    }

    /**
     * {@inheritdoc}
     */
    public function setSteps(array $steps): void
    {
        foreach ($steps as $step) {
            if (!$step instanceof StepInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected %s instance, "%s" given.',
                    StepActionInterface::class, \is_object($step) ? \get_class($step) : \gettype($step)
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
    public function getCurrentCharacter(): array
    {
        return $this->getSession()->get('character.'.$this->getManagerName(), []) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCharacterProperty(string $key = null)
    {
        if (null === $key) {
            if (!$this->step instanceof StepInterface) {
                throw new \InvalidArgumentException(sprintf(
                    'To get current step you need to use %s:%s method and inject a StepInterface instance.',
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
        return $this->goToStep($this->step->getNumber() + 1);
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
            throw new \InvalidArgumentException('Cannot use '.__METHOD__.' if no router is injected in AbstractStepAction.');
        }

        foreach ($this->steps as $step) {
            if ($step->getNumber() === $stepNumber) {
                $this->getSession()->set('step.'.$this->getManagerName(), $stepNumber);

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

        $this->getSession()->set('step.'.$this->getManagerName(), $this->step->getNumber());
        $this->getSession()->set('character.'.$this->getManagerName(), $character);
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
        } elseif (\count($msgParams)) {
            $msg = strtr($msg, $msgParams);
        }

        $session = $this->getSession();

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
            throw new \RuntimeException('Request is not set in step action.');
        }

        return $this->request;
    }

    protected function getSession(): Session
    {
        $session = $this->getRequest()->getSession();

        if (!($session instanceof Session)) {
            throw new \RuntimeException('The session must be available to manage characters. Did you forget to enable the session in the framework?');
        }

        return $session;
    }

    protected function getManagerName(): string
    {
        if (!$this->step instanceof StepInterface) {
            throw new \InvalidArgumentException(sprintf(
                'To get current step you need to use %s:%s method and inject a StepInterface instance.',
                __CLASS__, 'setStep'
            ));
        }

        return $this->step->getManagerName();
    }
}
