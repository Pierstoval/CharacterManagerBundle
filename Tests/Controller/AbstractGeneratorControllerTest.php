<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Controller\GeneratorController;
use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistryInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use \Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\RequestTestTrait;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractGeneratorControllerTest extends TestCase
{
    use RequestTestTrait;

    /**
     * @param MockObject|StepResolverInterface    $resolver
     * @param MockObject|TranslatorInterface      $translator
     * @param MockObject|RouterInterface          $router
     * @param MockObject|ActionsRegistryInterface $actionsRegistry
     *
     * @return GeneratorController
     */
    protected function createController(
        StepResolverInterface $resolver = null,
        RouterInterface $router = null,
        TranslatorInterface $translator = null,
        ActionsRegistryInterface $actionsRegistry = null
    ): GeneratorController {
        if (null === $resolver) {
            $resolver = $this->createMock(StepResolverInterface::class);
        }
        if (null === $router) {
            $router = $this->createMock(RouterInterface::class);
        }
        if (null === $translator) {
            $translator = $this->createMock(TranslatorInterface::class);
        }
        if (null === $actionsRegistry) {
            $actionsRegistry = $this->createMock(ActionsRegistryInterface::class);
        }

        return new GeneratorController($resolver, $actionsRegistry, $router, $translator);
    }

    protected function createManagerConfiguration(string $managerName, array $steps = []): array
    {
        $manager = [
            'character_class' => CharacterStub::class,
            'steps' => [],
        ];

        // Will be used to populate missing data
        $baseStep = [
            'action'         => ConcreteAbstractActionStub::class,
            'label'          => '',
            'number'         => 1,
            'name'           => '01',
            'manager_name'   => $managerName,
            'dependencies'   => [],
            'onchange_clear' => [],
        ];

        $manager['steps'] = $steps ?: [
            '01' => $baseStep,
        ];

        $i = 1;
        foreach ($manager['steps'] as $name => &$step) {
            $step = ['name' => $name, 'number' => $i++] + $step + $baseStep;
        }

        return $manager;
    }
}
