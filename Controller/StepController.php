<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Controller;

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
    public function indexAction()
    {
        $steps = $this->getParameter('pierstoval_character_manager.steps');

        reset($steps);
        $firstStep = current($steps);

        return $this->redirectToRoute('pierstoval_character_generator_step', [
            'step' => $firstStep['name'],
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
     * @Route("/generate/{step}", requirements={"step" = "[\w-]+"}, name="pierstoval_character_generator_step")
     *
     * @param Request $request
     *
     * @return array|RedirectResponse|Response
     */
    public function stepAction($step, Request $request)
    {
        $session = $this->get('session');

        //Si le personnage n'existe pas dans la session, on le crée
        if (!$session->get('character')) {
            $session->set('character', []);
        }

        return new Response('Todo');

        // TODO

        $character = $session->get('character');

        $this->steps = $this->getParameter('pierstoval_character_manager.steps');

        for ($i = 1; $i <= $step->getStep(); ++$i) {
            $stepName = $this->steps[$i]->getStep().'.'.$this->steps[$i]->getSlug();
            if (!array_key_exists($stepName, $character) && $step->getStep() > $this->steps[$i]->getStep()) {
                return $this->_goToStep($this->steps[$i]->getStep());
            }
        }

        /**
         * @var StepLoader
         */
        $stepLoader = $this->get('corahn_rin_generator.steps_loader');

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
