<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Controller;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Exception\StepNotFoundException;
use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistryInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class GeneratorController
{
    /**
     * @var StepResolverInterface
     */
    private $stepsResolver;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ActionsRegistryInterface
     */
    private $actionsRegistry;

    public function __construct(
        StepResolverInterface $resolver,
        ActionsRegistryInterface $actionsRegistry,
        RouterInterface $router,
        TranslatorInterface $translator = null
    ) {
        $this->stepsResolver = $resolver;
        $this->translator = $translator;
        $this->router = $router;
        $this->actionsRegistry = $actionsRegistry;
    }

    public function indexAction(Request $request, string $manager = null): RedirectResponse
    {
        $session = $this->getSession($request);

        try {
            $step = $this->stepsResolver->resolveNumber($session->get('step', 1), $manager);
        } catch (StepNotFoundException $e) {
            throw new NotFoundHttpException('No step found to start the generator.', $e);
        }

        $routeParams = ['requestStep' => $step->getName()];

        if ($manager) {
            $routeParams['manager'] = $manager;
        }

        return new RedirectResponse($this->router->generate('pierstoval_character_generator_step', $routeParams));
    }

    public function resetCharacterAction(Request $request): RedirectResponse
    {
        $session = $this->getSession($request);

        $session->set('character', []);
        $session->set('step', 1);
        $session->getFlashBag()->add('success', $this->trans('steps.reset.character', [], 'PierstovalCharacterManager'));

        return new RedirectResponse($this->router->generate('pierstoval_character_generator_index'));
    }

    public function resetStepAction(Request $request, string $requestStep, string $manager = null): RedirectResponse
    {
        $session = $this->getSession($request);

        $resolvedManagerName = $this->stepsResolver->resolveManagerName($manager);

        try {
            $step = $this->stepsResolver->resolve($requestStep, $resolvedManagerName);
        } catch (StepNotFoundException $e) {
            throw new NotFoundHttpException('Step not found.', $e);
        }

        $character = $session->get('character', [$resolvedManagerName => []]);
        unset($character[$resolvedManagerName][$step->getName()]);

        foreach ($step->getOnchangeClear() as $stepToClear) {
            unset($character[$resolvedManagerName][$stepToClear]);
        }

        $session->set('character', $character);

        $session->getFlashBag()->add('success', $this->trans('steps.reset.step', [], 'PierstovalCharacterManager'));

        $routeParams = ['requestStep' => $step->getName()];

        if ($manager) {
            $routeParams['manager'] = $manager;
        }

        return new RedirectResponse($this->router->generate('pierstoval_character_generator_step', $routeParams));
    }

    public function stepAction(Request $request, string $requestStep, string $manager = null): Response
    {
        $session = $this->getSession($request);

        $resolvedManagerName = $this->stepsResolver->resolveManagerName($manager);

        try {
            $step = $this->stepsResolver->resolve($requestStep, $resolvedManagerName);
        } catch (StepNotFoundException $e) {
            throw new NotFoundHttpException('Step not found.', $e);
        }

        $character = $session->get('character', [$resolvedManagerName => []]);

        // Make sure that dependencies exist, else redirect to first step with a message.
        foreach ($step->getDependencies() as $stepName) {
            if (!isset($character[$resolvedManagerName][$stepName])) {
                $msg = $this->trans('steps.dependency_not_set', [
                    '%current_step%' => $step->getLabel(),
                    '%dependency%' => $this->stepsResolver->resolve($stepName, $resolvedManagerName)->getLabel(),
                ], 'PierstovalCharacterManager');
                $session->getFlashBag()->add('error', $msg);

                return new RedirectResponse($this->router->generate('pierstoval_character_generator_index'));
            }
        }

        /** @var StepActionInterface $action */
        $action = $this->actionsRegistry->getAction($step->getName());

        $action->setRequest($request);

        // Execute the action and expect a response. Symfony will do the rest.
        return $action->execute();
    }

    private function trans(string $message, array $parameters = [], string $translationDomain = null): string
    {
        if (!$this->translator) {
            return \strtr($message, $parameters);
        }

        return (string) $this->translator->trans($message, $parameters, $translationDomain ?: 'messages');
    }

    private function getSession(Request $request): Session
    {
        $session = $request->getSession();

        if (!($session instanceof Session)) {
            throw new \RuntimeException('Session is mandatory when using the character generator.');
        }

        return $session;
    }
}
