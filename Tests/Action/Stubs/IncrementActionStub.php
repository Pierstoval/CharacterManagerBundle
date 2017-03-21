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

final class IncrementActionStub extends StepAction
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        return $this->nextStep();
    }
}
