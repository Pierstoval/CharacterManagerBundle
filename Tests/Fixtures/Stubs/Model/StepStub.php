<?php

/**
 * This file is part of the CharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;

class StepStub extends Step
{
    public static function createStub(array $data = [])
    {
        $data = array_merge([
            'number' => 0,
            'name' => 'test_step',
            'label' => 'Test step',
            'action' => ConcreteAbstractActionStub::class,
            'manager_name' => 'test_manager',
            'onchange_clear' => [], // On cache clear
            'dependencies' => [] // Dependencies
        ], $data);

        return static::createFromData($data);
    }
}
