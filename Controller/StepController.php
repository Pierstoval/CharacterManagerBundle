<?php

/*
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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class StepController extends Controller
{
    /**
     * @Route("/generate", name="pierstoval_character_generator_index")
     *
     * @return RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $steps = $this->getParameter('pierstoval_character_manager.steps');

        $stepName = null;

        $stepNumber = $request->getSession()->get('step');
        if (null !== $stepNumber) {
            foreach ($steps as $step) {
                if ($step['step'] === $stepNumber) {
                    $stepName = $step['name'];
                }
            }
        } else {
            reset($steps);
            $firstStep = current($steps);
            $stepName  = $firstStep['name'];
        }

        if (!$stepName) {
            throw $this->createNotFoundException('No step found to start the generator.');
        }

        return $this->redirectToRoute('pierstoval_character_generator_step', [
            'requestStep' => $stepName,
        ]);
    }

    /**
     * @Route("/reset/", name="pierstoval_character_generator_reset")
     *
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

        return $this->redirectToRoute('pierstoval_character_generator_index');
    }

    /**
     * @Route("/reset/{requestStep}", name="pierstoval_character_generator_reset_step")
     *
     * @param string $requestStep
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function resetStepAction($requestStep, Request $request)
    {
        $stepsArray = $this->getParameter('pierstoval_character_manager.steps');

        if (!array_key_exists($requestStep, $stepsArray)) {
            throw $this->createNotFoundException('Step not found.');
        }

        $step = Step::createFromData($stepsArray[$requestStep]);

        /** @var Session $session */
        $session = $request->getSession();

        $character = $session->get('character');
        unset($character[$step->getName()]);

        foreach ($step->getOnchangeClear() as $step) {
            unset($character[$step]);
        }

        $session->set('character', $character);

        $session->getFlashBag()->add('success', 'L\'étape a été correctement réinitialisée !');

        return $this->redirectToRoute('pierstoval_character_generator_step', ['requestStep' => $requestStep]);
    }

    /**
     * @Route("/generate/{requestStep}", requirements={"step" = "[\w-]+"}, name="pierstoval_character_generator_step")
     *
     * @param string  $requestStep
     * @param Request $request
     *
     * @return Response
     */
    public function stepAction($requestStep, Request $request)
    {
        $stepsArray = $this->getParameter('pierstoval_character_manager.steps');

        if (!array_key_exists($requestStep, $stepsArray)) {
            throw $this->createNotFoundException('Step not found.');
        }

        /** @var Step[] $steps */
        $steps = [];

        // Transform array of array in array of value objects.
        foreach ($stepsArray as $key => $stepArray) {
            $steps[$key] = Step::createFromData($stepArray);
        }

        /** @var Step $step */
        $step = $steps[$requestStep];

        /** @var Session $session */
        $session = $request->getSession();
        $character = $session->get('character');

        // Make sure that dependencies exist, else redirect to first step with a message.
        foreach ($step->getDependencies() as $id) {
            if (!isset($character[$id])) {
                $msg = $this->get('translator')->trans('pierstoval_character_manager.steps.dependency_not_set', [
                    '%current_step%' => $step->getName(),
                    '%dependency%' => $id,
                ], 'PierstovalCharacterManager');
                $session->getFlashBag()->add('error', $msg);
                $this->redirectToRoute('pierstoval_character_generator_index');
            }
        }

        $actionId = $step->getAction();

        /** @var StepActionInterface $action */
        $action = $this->has($actionId) ? $this->get($actionId) : new $actionId();

        // In case of, update action class.
        if (!$action->getCharacterClass()) {
            $action->setCharacterClass($this->getParameter('pierstoval_character_manager.character_class'));
        }

        $action->setRequest($request);
        $action->setStep($step);
        $action->setSteps($steps);

        // Execute the action and expect a response. Symfony will do the rest.
        return $action->execute();
    }
}
