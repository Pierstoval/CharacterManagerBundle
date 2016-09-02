<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Symfony\Component\HttpFoundation\Request;

class StubStep implements StepActionInterface
{

    public function execute()
    {
    }

    public function setStep(Step $step)
    {
    }

    public function setSteps(array $steps)
    {
    }

    public function setRequest(Request $request)
    {
    }

    public function getCurrentCharacter()
    {
    }

    public function getCharacterProperty($key = null)
    {
    }

    public function setCharacterClass($class)
    {
    }

    public function getCharacterClass()
    {
    }
}
