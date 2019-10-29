<?php

declare(strict_types=1);

/*
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
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model\StepStub;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StepActionStub implements StepActionInterface
{
    private $step;

    public function execute(): Response
    {
        return new Response('Stub response');
    }

    public function getStep(): StepInterface
    {
        return $this->step ?: ($this->step = StepStub::createStub());
    }

    public function setRequest(Request $request): void
    {
    }

    public function configure(string $managerName, string $stepName, string $characterClassName, StepResolverInterface $resolver): void
    {
    }

    public function stepName(): string
    {
        return $this->getStep()->getName();
    }
}
