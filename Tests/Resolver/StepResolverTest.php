<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolver;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;

class StepResolverTest extends TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No character managers to resolve configuration for.
     */
    public function test manager steps with empty config throws exception()
    {
        $resolver = new StepResolver();

        $resolver->getManagerSteps();
    }

    public function test manager steps with single config option()
    {
        $stepsArray = [
            'one' => [
                'number'         => 1,
                'name'           => '01',
                'action'         => ConcreteAbstractActionStub::class,
                'label'          => '',
                'onchange_clear' => [],
                'dependencies'   => [],
                'manager_name'   => 'main_manager',
            ]
        ];

        $stepsObjects = [];

        foreach ($stepsArray as $name => $step) {
            $stepsObjects[$name] = Step::createFromData($step);
        }

        $resolver = new StepResolver(['main_manager' => ['steps' => $stepsArray]]);

        static::assertEquals($stepsObjects, $resolver->getManagerSteps('main_manager'));
    }

    /**
     * @expectedException \Pierstoval\Bundle\CharacterManagerBundle\Exception\StepNotFoundException
     * @expectedExceptionMessage Step non_existent_step does not exist in manager main_manager.
     */
    public function test resolve non existent step name should throw exception()
    {
        $resolver = new StepResolver([
            'main_manager' => [
                'steps' => [
                    'step_1' => [
                        'action' => ConcreteAbstractActionStub::class,
                        'name' =>  'step_1',
                        'label' =>  'Step 1',
                        'dependencies' =>  [],
                        'manager_name' => 'main_manager',
                        'onchange_clear' =>  [],
                        'number' =>  1,
                    ],
                ],
            ],
        ]);

        $resolver->resolve('non_existent_step');
    }

    /**
     * @expectedException \Pierstoval\Bundle\CharacterManagerBundle\Exception\StepNotFoundException
     * @expectedExceptionMessage Step number 5 does not exist in manager main_manager.
     */
    public function test resolve non existent step number should throw an exception()
    {
        $resolver = new StepResolver([
            'main_manager' => [
                'steps' => [
                    'step_1' => [
                        'action' => ConcreteAbstractActionStub::class,
                        'name' =>  'step_1',
                        'label' =>  'Step 1',
                        'dependencies' =>  [],
                        'manager_name' => 'main_manager',
                        'onchange_clear' =>  [],
                        'number' =>  1,
                    ],
                ],
            ],
        ]);

        $resolver->resolveNumber(5);
    }

    public function test resolve step name should return correct step values()
    {
        $step1 = [
            'number' => 0,
            'name' =>  'step_1',
            'action' => ConcreteAbstractActionStub::class,
            'label' =>  'Step 1',
            'manager_name' => 'main_manager',
            'onchange_clear' =>  [],
            'dependencies' =>  [],
        ];

        $resolver = new StepResolver([
            'main_manager' => [
                'steps' => [
                    'step_1' => $step1,
                ],
            ],
        ]);

        $resolvedStep = $resolver->resolve('step_1');

        static::assertNotNull($resolvedStep);
        static::assertSame($step1['number'], $resolvedStep->getNumber());
        static::assertSame($step1['name'], $resolvedStep->getName());
        static::assertSame($step1['label'], $resolvedStep->getLabel());
        static::assertSame($step1['action'], $resolvedStep->getAction());
        static::assertSame($step1['manager_name'], $resolvedStep->getManagerName());
        static::assertSame($step1['onchange_clear'], $resolvedStep->getOnchangeClear());
        static::assertSame($step1['dependencies'], $resolvedStep->getDependencies());
    }

    public function test resolve step number should return correct step()
    {
        $step1 = [
            'number' => 0,
            'name' =>  'step_1',
            'action' => ConcreteAbstractActionStub::class,
            'label' =>  'Step 1',
            'manager_name' => 'main_manager',
            'onchange_clear' =>  [],
            'dependencies' =>  [],
        ];

        $resolver = new StepResolver([
            'main_manager' => [
                'steps' => [
                    'step_1' => $step1,
                ],
            ],
        ]);

        $resolvedStep = $resolver->resolveNumber(0);

        static::assertNotNull($resolvedStep);
        static::assertSame($step1['number'], $resolvedStep->getNumber());
        static::assertSame($step1['name'], $resolvedStep->getName());
        static::assertSame($step1['label'], $resolvedStep->getLabel());
        static::assertSame($step1['action'], $resolvedStep->getAction());
        static::assertSame($step1['manager_name'], $resolvedStep->getManagerName());
        static::assertSame($step1['onchange_clear'], $resolvedStep->getOnchangeClear());
        static::assertSame($step1['dependencies'], $resolvedStep->getDependencies());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Step non_existent_step does not exist in manager test.
     */
    public function test resolve with no steps should throw exception()
    {
        $resolver = new StepResolver(['test' => ['steps' => []]]);

        $resolver->resolve('non_existent_step');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Step number 0 does not exist in manager test.
     */
    public function test resolve number with no steps should throw exception()
    {
        $resolver = new StepResolver(['test' => ['steps' => []]]);

        $resolver->resolveNumber(0);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Manager invalid_manager does not exist, or is not initialized yet.
     */
    public function test resolve invalid manager should throw exception()
    {
        $resolver = new StepResolver([
            'main_manager' => [
                'steps' => [
                    'step_1' => [
                        'action'         => '',
                        'label'          => '',
                        'number'         => 1,
                        'name'           => '',
                        'manager_name'   => '',
                        'dependencies'   => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
        ]);

        $resolver->resolve('', 'invalid_manager');
    }

    public function test resolve multiple manager names with same steps names()
    {
        $resolver = new StepResolver([
            'manager_one' => [
                'character_class' => '',
                'steps'           => [
                    '01' => [
                        'action'         => '',
                        'label'          => '',
                        'number'         => 1,
                        'name'           => '',
                        'manager_name'   => 'manager_one',
                        'dependencies'   => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
            'manager_two' => [
                'character_class' => '',
                'steps'           => [
                    '01' => [
                        'action'         => '',
                        'label'          => '',
                        'number'         => 1,
                        'name'           => '',
                        'manager_name'   => 'manager_two',
                        'dependencies'   => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
        ]);

        $stepFromManagerOne = $resolver->resolve('01', 'manager_one');
        static::assertNotNull($stepFromManagerOne);
        static::assertSame('manager_one', $stepFromManagerOne->getManagerName());

        $stepFromManagerTwo = $resolver->resolve('01', 'manager_two');
        static::assertNotNull($stepFromManagerTwo);
        static::assertSame('manager_two', $stepFromManagerTwo->getManagerName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You did not specify which character manager you want to get the steps from, and you have more than one manager. Possible choices: manager_one, manager_two
     */
    public function test resolve with no manager name when multiple managers configured should throw exception()
    {
        $resolver = new StepResolver([
            'manager_one' => [
                'character_class' => '',
                'steps'           => [
                    '01' => [
                        'action'         => '',
                        'label'          => '',
                        'number'         => 1,
                        'name'           => '',
                        'manager_name'   => '',
                        'dependencies'   => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
            'manager_two' => [
                'character_class' => '',
                'steps'           => [
                    '01' => [
                        'action'         => '',
                        'label'          => '',
                        'number'         => 1,
                        'name'           => '',
                        'manager_name'   => '',
                        'dependencies'   => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
        ]);

        $resolver->resolve('01');
    }
}
