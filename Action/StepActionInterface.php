<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface StepActionInterface
{
    /**
     * Any step action is like a controller action: we inject the request to it, and we need a response.
     * As we can have tons of steps, it's much better to rely on action pattern,
     *   rather than one controller with tons of methods.
     *
     * @return Response
     */
    public function execute();

    /**
     * Allows using the Step value object in the action.
     *
     * @param Step $step
     */
    public function setStep(Step $step);

    /**
     * Allow having all steps in the action, to redirect to next action.
     *
     * @param Step[] $steps
     */
    public function setSteps(array $steps);

    /**
     * Current Request object that will be used in the Step action.
     *
     * @param Request $request
     */
    public function setRequest(Request $request);

    /**
     * Return the current character that is built in the steps process.
     *
     * @return mixed
     */
    public function getCurrentCharacter();

    /**
     * Get a property from the current character.
     * Default null can be managed to retrieve current step's name.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function getCharacterProperty($key = null);

    /**
     * Any action must be injected the character class, because are not always defined as services.
     * Called automatically by compiler pass if service is tagged.
     *
     * @param string $class
     */
    public function setCharacterClass($class);

    /**
     * Get configured character class.
     *
     * @return string
     */
    public function getCharacterClass();
}
