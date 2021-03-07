<?php

use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;

return [
    'input' => [
        'pierstoval_character_manager' => [
            'managers' => [
                'main' => [
                    'character_class' => CharacterStub::class,
                    'steps' => [
                        'named_step' => [
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
                        'named_step' => [
                            'action' => 'steps.default',
                            'dependencies' => [],
                            'label' => 'Named Step',
                            'manager_name' => 'main',
                            'name' => 'named_step',
                            'number' => 1,
                            'onchange_clear' => [],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
