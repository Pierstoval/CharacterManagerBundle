<?php

declare(strict_types=1);

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Doctrine\ORM\EntityManagerInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

abstract class AbstractStepAction implements StepActionInterface
{
    /**
     * Used when you need to change the translation domain used in controller-generated messages.
     */
    protected static $translationDomain = 'PierstovalCharacterBundle';

    /** @var string */
    protected $class;

    /** @var Request */
    protected $request;

    /** @var StepInterface */
    protected $step;

    /** @var StepInterface[] */
    protected $steps = [];

    /** @var string */
    protected $stepName;

    /** @var string */
    protected $managerName;

    /** @var RouterInterface */
    protected $router;

    /** @var EntityManagerInterface */
    protected $em;

    /** @var Environment */
    protected $twig;

    /** @var TranslatorInterface */
    protected $translator;

    private $configured = false;

    public function configure(string $managerName, string $stepName, string $characterClassName, StepResolverInterface $resolver): void
    {
        if ($this->configured) {
            throw new \RuntimeException('Cannot reconfigure an already configured step action.');
        }

        if (!\class_exists($characterClassName) || !\is_subclass_of($characterClassName, CharacterInterface::class, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Step action must be a valid class implementing %s. "%s" given.',
                CharacterInterface::class,
                \class_exists($characterClassName) ? $characterClassName : \gettype($characterClassName)
            ));
        }

        $this->class = $characterClassName;
        $this->managerName = $managerName;
        $this->step = $resolver->resolve($stepName, $managerName);
        $this->stepName = $stepName;
        $this->setSteps($resolver->getManagerSteps($managerName));

        $this->configured = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    public function setObjectManager(EntityManagerInterface $em): void
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

    public function getStep(): StepInterface
    {
        $this->checkConfigured();

        return $this->step;
    }

    public function stepName(): string
    {
        $this->checkConfigured();

        return $this->stepName;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCurrentCharacter(): array
    {
        return $this->getSession()->get('character.'.$this->managerName, []) ?: [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCharacterProperty(string $key = null)
    {
        if (null === $key) {
            $key = $this->getStep()->getName();
        }

        $character = $this->getCurrentCharacter();

        return $character[$key] ?? null;
    }

    protected function nextStep(): RedirectResponse
    {
        return $this->goToStep($this->getStep()->getNumber() + 1);
    }

    /**
     * Redirects to a specific step and updates the session.
     */
    protected function goToStep(int $stepNumber): RedirectResponse
    {
        $this->checkConfigured();

        if (!$this->router) {
            throw new \InvalidArgumentException('Cannot use '.__METHOD__.' if no router is injected in AbstractStepAction.');
        }

        foreach ($this->steps as $step) {
            if ($step->getNumber() === $stepNumber) {
                $this->getSession()->set('step.'.$this->managerName, $stepNumber);

                return new RedirectResponse($this->router->generate('pierstoval_character_generator_step', ['requestStep' => $step->getName()]));
            }
        }

        throw new \InvalidArgumentException('Invalid step: '.$stepNumber);
    }

    protected function updateCharacterStep($value): void
    {
        $character = $this->getCurrentCharacter();

        $character[$this->getStep()->getName()] = $value;

        foreach ($this->getStep()->getOnchangeClear() as $stepToDisable) {
            unset($character[$stepToDisable]);
        }

        $this->getSession()->set('step.'.$this->managerName, $this->getStep()->getNumber());
        $this->getSession()->set('character.'.$this->managerName, $character);
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

        $msg = $this->translator
            ? $this->translator->trans($msg, $msgParams, static::$translationDomain)
            : \strtr($msg, $msgParams)
        ;

        $flashbag = $this->getSession()->getFlashBag();

        // Add the message manually.
        $existingMessages = $flashbag->peek($type);
        $existingMessages[] = $msg;

        // And avoid having the same message multiple times.
        $flashbag->set($type, \array_unique($existingMessages));

        return $this;
    }

    protected function getRequest(): Request
    {
        $this->checkConfigured();

        if (!$this->request) {
            throw new \RuntimeException('Request is not set in step action.');
        }

        return $this->request;
    }

    protected function getSession(): Session
    {
        if (!($this->getRequest()->hasSession())) {
            throw new \RuntimeException('The session must be available to manage characters. Did you forget to enable the session in the framework?');
        }

        return $this->getRequest()->getSession();
    }

    /**
     * {@inheritdoc}
     */
    private function setSteps(array $steps): void
    {
        foreach ($steps as $step) {
            if (!$step instanceof StepInterface) {
                throw new \InvalidArgumentException(\sprintf(
                    'Expected %s instance, "%s" given.',
                    StepActionInterface::class,
                    \is_object($step) ? \get_class($step) : \gettype($step)
                ));
            }
        }

        $this->steps = $steps;
    }

    private function checkConfigured(): void
    {
        if (!$this->configured) {
            throw new \RuntimeException('Step action is not configured. Did you forget to run the "configure()" method?');
        }
    }
}
