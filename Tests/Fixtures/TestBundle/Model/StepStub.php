<?php

/**
 * This file is part of the CharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Model;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\DefaultTestStep;

class StepStub extends Step
{
    public function __construct()
    {
        parent::__construct(0, 'test_step', DefaultTestStep::class, 'test_step', [], []);
    }
}
