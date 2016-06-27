<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Controller;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     */
    public function resetAction()
    {
        $session = $this->get('session');
        $session->set('character', []);
        $session->set('step', 1);
        $session->getFlashBag()->add('success', 'Le personnage en cours de création a été réinitialisé !');

        return $this->redirect($this->generateUrl('pierstoval_character_generator_index'));
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

        $actionId = $step->getAction();

        /** @var StepActionInterface $action */
        $action = $this->has($actionId) ? $this->get($actionId) : new $actionId();

        $action->setClass($this->getParameter('pierstoval_character_manager.character_class'));
        $action->setRequest($request);
        $action->setStep($step);
        $action->setSteps($steps);

        // Execute the action and expect a response. Symfony will do the rest.
        return $action->execute();
    }
}
