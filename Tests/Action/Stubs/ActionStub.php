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

final class ActionStub extends StepAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function nextStep()
    {
        return parent::nextStep();
    }

    /**
     * {@inheritdoc}
     */
    public function goToStep($stepNumber)
    {
        return parent::goToStep($stepNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCharacterStep($value)
    {
        parent::updateCharacterStep($value);
    }

    /**
     * {@inheritdoc}
     */
    public function flashMessage($msg, $type = null, array $msgParams = [])
    {
        return parent::flashMessage($msg, $type, $msgParams);
    }
}
