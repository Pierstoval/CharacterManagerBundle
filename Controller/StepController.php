<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Controller;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistry;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepActionResolver;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class StepController
{
    /**
     * @var Step[]
     */
    private $steps;

    /**
     * @var StepActionResolver
     */
    private $actionResolver;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var ActionsRegistry
     */
    private $actionsRegistry;

    public function __construct(StepActionResolver $actionResolver, TranslatorInterface $translator, RouterInterface $router, ActionsRegistry $actionsRegistry)
    {
        $this->steps = $actionResolver->getSteps();
        $this->actionResolver = $actionResolver;
        $this->translator = $translator;
        $this->router = $router;
        $this->actionsRegistry = $actionsRegistry;
    }

    /**
     * @return RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $stepName = null;

        $stepNumber = $request->getSession()->get('step');
        if (null !== $stepNumber) {
            foreach ($this->steps as $step) {
                if ($step->getStep() === $stepNumber) {
                    $stepName = $step->getName();
                }
            }
        } else {
            reset($steps);
            $firstStep = current($steps);
            $stepName  = $firstStep->getName();
        }

        if (!$stepName) {
            throw new NotFoundHttpException('No step found to start the generator.');
        }

        return new RedirectResponse($this->router->generate('pierstoval_character_generator_step', [
            'requestStep' => $stepName,
        ]));
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function resetAction(Request $request)
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->set('character', []);
        $session->set('step', 1);
        $session->getFlashBag()->add('success', 'Le personnage en cours de création a été réinitialisé !');

        return new RedirectResponse($this->router->generate('pierstoval_character_generator_index'));
    }

    /**
     * @param string $requestStep
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function resetStepAction($requestStep, Request $request)
    {
        if (!array_key_exists($requestStep, $this->steps)) {
            throw new NotFoundHttpException('Step not found.');
        }

        $step = $this->steps[$requestStep];

        /** @var Session $session */
        $session = $request->getSession();

        $character = $session->get('character');
        unset($character[$step->getName()]);

        foreach ($step->getOnchangeClear() as $step) {
            unset($character[$step]);
        }

        $session->set('character', $character);

        $session->getFlashBag()->add('success', 'L\'étape a été correctement réinitialisée !');

        return new RedirectResponse($this->router->generate('pierstoval_character_generator_step', ['requestStep' => $requestStep]));
    }

    /**
     * @param string  $requestStep
     * @param Request $request
     *
     * @return Response
     */
    public function stepAction($requestStep, Request $request)
    {
        $resolver = $this->actionResolver;

        $steps = $resolver->getSteps();
        $step = $resolver->resolve($requestStep);

        if (null === $step) {
            throw new NotFoundHttpException('Step not found.');
        }

        $step = $steps[$requestStep];

        /** @var Session $session */
        $session = $request->getSession();
        $character = $session->get('character');

        // Make sure that dependencies exist, else redirect to first step with a message.
        foreach ($step->getDependencies() as $id) {
            if (!isset($character[$id])) {
                $msg = $this->translator->trans('pierstoval_character_manager.steps.dependency_not_set', [
                    '%current_step%' => $step->getName(),
                    '%dependency%' => $id,
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
}
