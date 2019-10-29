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

use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Mostly here to make protected methods public in order to test them.
 */
class ConcreteAbstractActionStub extends AbstractStepAction
{
    public function execute(): Response
    {
        return new Response('Stub response based on abstract class');
    }

    public function nextStep(): RedirectResponse
    {
        return parent::nextStep();
    }

    public function updateCharacterStep($value): void
    {
        parent::updateCharacterStep($value);
    }

    public function flashMessage(string $msg, string $type = null, array $msgParams = []): parent
    {
        return parent::flashMessage($msg, $type, $msgParams);
    }

    public function goToStep(int $stepNumber): RedirectResponse
    {
        return parent::goToStep($stepNumber);
    }

    public function getCurrentCharacter(): array
    {
        return parent::getCurrentCharacter();
    }

    public function getCharacterProperty(string $key = null)
    {
        return parent::getCharacterProperty($key);
    }

    public function getSession(): Session
    {
        return parent::getSession();
    }
}
