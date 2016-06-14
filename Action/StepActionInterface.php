<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface StepActionInterface
{

    /**
     * Any step action is like a controller action: we inject the request to it, and we need a response.
     * As we can have tons of steps, it's much better to rely on action pattern,
     *   rather than one controller with tons of methods.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function execute(Request $request);

    /**
     * Any action must be injected the character class,
     *   because are not always defined as services.
     *
     * @param string $class
     */
    public function setClass($class);
}
