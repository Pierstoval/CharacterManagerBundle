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
                    ],
                ],
            ],
        ],
    ],
];
