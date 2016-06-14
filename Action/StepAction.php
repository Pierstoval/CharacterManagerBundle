<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Action;

use Symfony\Component\HttpFoundation\Request;

abstract class StepAction
{
    abstract public function execute(Request $request);
}
