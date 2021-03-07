<?php

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
                        ],
                        'step_2' => [
                            'action' => 'steps.default',
                            'dependencies' => [
                                'step_1',
                            ],
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
                            'onchange_clear' => [],
                        ],
                        'step_2' => [
                            'action' => 'steps.default',
                            'dependencies' => [
                                'step_1',
                            ],
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
