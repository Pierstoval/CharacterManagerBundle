<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Controller;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
            $stepName = $firstStep['name'];
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
            $steps[$key] = new Step(
                $stepArray['step'],
                $key,
                $stepArray['action'],
                $stepArray['label'],
                $stepArray['steps_to_disable_on_change']
            );
        }

        /** @var Step $step */
        $step = $steps[$requestStep];

        // If the character does not exist in session yet, create an empty one.
        // This character is only a stub, an array of data.
        // At the final step, it can be sent to the Character entity,
        //   via the Character::createFromGenerator() method.
        $session = $request->getSession();
        if (!$session->get('character')) {
            $session->set('character', []);
        }

        $actionId = $step->getAction();

        /** @var StepActionInterface $action */
        $action = $this->has($actionId) ? $this->get($actionId) : new $actionId();

        $action->setClass($this->getParameter('pierstoval_character_manager.character_class'));
        $action->setRequest($request);
        $action->setStep($step);
        $action->setSteps($steps);

        // Execute the action and expect a response. Symfony will do the rest.
        return $action->execute();

        /**
         * @var StepLoader
         */
        $stepLoader = $this->get('corahnrin_generator.steps_loader');

        $stepLoader->initialize($this, $session, $request, $step, $this->steps);

        if ($stepLoader->exists()) {
            //Si la méthode existe on l'exécute pour lancer l'analyse de l'étape
            $data = $stepLoader->load();

            if (is_object($data) && is_a($data, '\Symfony\Component\HttpFoundation\RedirectResponse')) {
                //Si data est un objet "RedirectResponse", c'est qu'on a exécuté nextStep() dans le loader
                $session->set('step', $step->getStep() + 1);

                return $data;
            } else {
                //Étape chargée
                $data['loaded_step'] = $step;

                //Fichier de la vue de l'étape
                $data['loaded_step_filename']
                    = $stepLoader->getViewsDirectory().':'.
                      '_step_'
                      .str_pad($step->getStep(), 2, '0', STR_PAD_LEFT)
                      .'_'
                      .$step->getSlug()
                      .'.html.twig';

                return $this->render('CorahnRinBundle:Generator:step_base.html.twig', $data);
            }
        }

        //Si la méthode n'existe pas, alors on a demandé une étape en trop (ou en moins)
        //Dans ce cas, on renvoie une erreur
        $msg = $this->get('translator')->trans('L\'étape %step% n\'a pas été trouvée...', ['%step%' => $step->getStep()], 'error.steps');
        throw $this->createNotFoundException($msg);
    }

    /**
     * @param Steps $step
     *
     * @return array
     */
    public function menuAction(Steps $step = null)
    {
        $actual_step = (int) $this->get('session')->get('step') ?: 1;
        $this->steps = $this->steps
            ?: $this->getDoctrine()->getManager()->getRepository('CorahnRinBundle:Steps')->findAll('step');
        $barWidth    = count($this->steps) ? ($actual_step / count($this->steps) * 100) : 0;

        return $this->render('@CorahnRin/Generator/menu.html.twig', [
            'steps'        => $this->steps,
            'session_step' => $actual_step,
            'bar_width'    => $barWidth,
            'loaded_step'  => $step,
        ]);
    }

    /*-------------------------------------------------------------------------
    ---------------------------------------------------------------------------
    ---------------------------- MÉTHODES INTERNES ----------------------------
    ---------------------------------------------------------------------------
    -------------------------------------------------------------------------*/

    /**
     * Redirige vers une étape.
     *
     * @param $stepNumber
     *
     * @return RedirectResponse
     *
     * @throws \InvalidArgumentException
     */
    public function _goToStep($stepNumber)
    {
        $step = null;
        $step = $this->steps
            ? $this->steps[$stepNumber]
            : $this->getDoctrine()->getManager()
                ->getRepository('CorahnRinBundle:Steps')
                ->findOneBy(['step' => $stepNumber])
        ;

        if ($step) {
            $url = $this->generateUrl('pierstoval_character_generator_step', [
                'step' => $step->getStep(),
                'slug' => $step->getSlug(),
            ]);
            $this->get('session')->set('step', $step->getStep());

            return $this->redirect($url);
        } else {
            $msg = $this->get('translator')->trans('Mauvaise étape redirigée.', [], 'error.steps');
            throw new \InvalidArgumentException($msg);
        }
    }
}
