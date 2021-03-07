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

use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;

return [
    'input' => [
        'pierstoval_character_manager' => [
            'managers' => [
                'main' => [
                    'character_class' => CharacterStub::class,
                    'steps' => [
                        'step_1' => [
                            'action' => 'steps.default',
                            'onchange_clear' => [
                                'step_2',
                            ],
                        ],
                        'step_2' => [
                            'action' => 'steps.default',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'output' => [
        'pierstoval_character_manager' => [
            'managers' => [
                'main' => [
                    'character_class' => CharacterStub::class,
                    'name' => 'main',
                    'steps' => [
                        'step_1' => [
                            'action' => 'steps.default',
                            'dependencies' => [],
                            'label' => 'Step 1',
                            'manager_name' => 'main',
                            'name' => 'step_1',
                            'number' => 1,
                            'onchange_clear' => [
                                'step_2',
                            ],
                        ],
                        'step_2' => [
                            'action' => 'steps.default',
                            'dependencies' => [],
                            'label' => 'Step 2',
                            'manager_name' => 'main',
                            'name' => 'step_2',
                            'number' => 2,
                            'onchange_clear' => [],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
