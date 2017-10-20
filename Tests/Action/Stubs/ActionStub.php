<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Action\Stubs;

use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class ActionStub extends StepAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(): Response
    {
        return new Response('ok');
    }

    /**
     * {@inheritdoc}
     */
    public function nextStep(): RedirectResponse
    {
        return parent::nextStep();
    }

    /**
     * {@inheritdoc}
     */
    public function goToStep(int $stepNumber): RedirectResponse
    {
        return parent::goToStep($stepNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCharacterStep($value): void
    {
        parent::updateCharacterStep($value);
    }

    /**
     * {@inheritdoc}
     */
    public function flashMessage(string $msg, string $type = null, array $msgParams = []): StepAction
    {
        return parent::flashMessage($msg, $type, $msgParams);
    }
}
