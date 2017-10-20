<?php

/**
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
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Model\StepStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StubStep implements StepActionInterface
{
    public function execute(): Response
    {
        return new Response();
    }

    public function setStep(Step $step): void
    {
    }

    public function setSteps(array $steps): void
    {
    }

    public function getStep(): Step
    {
        return new StepStub();
    }

    public function getSteps()
    {
    }

    public function setRequest(Request $request): void
    {
    }

    public function getCurrentCharacter()
    {
    }

    public function getCharacterProperty(string $key = null)
    {
    }

    public function setCharacterClass(string $class): void
    {
    }

    public function getCharacterClass(): string
    {
        return CharacterStub::class;
    }
}
