<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model\StepStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StepActionStub implements StepActionInterface
{
    public function execute(): Response
    {
        return new Response('Stub response');
    }

    public function setStep(StepInterface $step): void
    {
    }

    public function setSteps(array $steps): void
    {
    }

    public function getStep(): StepInterface
    {
        return new StepStub();
    }

    public function getSteps(): array
    {
        return [];
    }

    public function setRequest(Request $request): void
    {
    }

    public function getCurrentCharacter(): array
    {
        return [];
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
