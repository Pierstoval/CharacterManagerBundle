<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepActionResolver;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\DefaultTestStep;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\StubStep;

class StepActionResolverTest extends TestCase
{
    public function test base configuration()
    {
        $resolver = new StepActionResolver([
            'step_1' => [
                'action' => StubStep::class,
                'name' =>  'step_1',
                'label' =>  'Step 1',
                'depends_on' =>  [],
                'onchange_clear' =>  [],
                'step' =>  1,
            ],
        ]);

        $step = $resolver->resolve('step_1');

        static::assertSame(StubStep::class, $step->getAction());
    }

    public function test resolve inexistent step should return null()
    {
        $resolver = new StepActionResolver([
            'step_1' => [
                'action' => StubStep::class,
                'name' =>  'step_1',
                'label' =>  'Step 1',
                'depends_on' =>  [],
                'onchange_clear' =>  [],
                'step' =>  1,
            ],
        ]);

        static::assertNull($resolver->resolve('inexistent_step'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No steps in resolver
     */
    public function test no configuration should throw exception()
    {
        $resolver = new StepActionResolver();

        $resolver->resolve('inexistent_step');
    }

    public function test retrieve all steps()
    {
        $resolver = new StepActionResolver([
            'step_1' => [
                'action' => StubStep::class,
                'name' =>  'step_1',
                'label' =>  'Step 1',
                'depends_on' =>  [],
                'onchange_clear' =>  [],
                'step' =>  1,
            ],
            'step_2' => [
                'action' => DefaultTestStep::class,
                'name' =>  'step_2',
                'label' =>  'Step 2',
                'depends_on' =>  [],
                'onchange_clear' =>  [],
                'step' =>  2,
            ],
        ]);

        $allSteps = $resolver->getSteps();

        $i = 1;
        static::assertCount(2, $allSteps);
        foreach ($allSteps as $key => $step) {
            static::assertSame($key, $step->getName());
            static::assertSame($i++, $step->getStep());
        }
    }
}
