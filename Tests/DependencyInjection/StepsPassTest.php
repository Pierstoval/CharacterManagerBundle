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

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler\StepsPass;
use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistry;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

/**
 * Test the extension and the compiler pass.
 */
class StepsPassTest extends TestCase
{
    /**
     * @dataProvider provideNonWorkingConfigurations
     */
    public function test compiler pass should not work if extension not processed(
        array $config,
        string $expectException,
        string $expectExceptionMessage,
        string $stepClass
    ): void {
        $this->expectException($expectException);
        $this->expectExceptionMessage($expectExceptionMessage);

        $stepsPass = new StepsPass();

        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass($stepClass)
        ;

        $container->setParameter('pierstoval_character_manager.managers', $config);

        $stepsPass->process($container);
    }

    public function provideNonWorkingConfigurations()
    {
        $dir = __DIR__.'/../Fixtures/App/compiler_pass_test_non_working/';

        $configFiles = \glob($dir.'compiler_config_*.yaml');

        \sort($configFiles);

        foreach ($configFiles as $file) {
            $config = Yaml::parse(\file_get_contents($file));
            yield \basename($file) => [
                $config['config'],
                $config['expected_exception'],
                $config['expected_exception_message'],
                $config['step_class'],
            ];
        }
    }

    public function provide container builder for services injection(): Generator
    {
        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass(ConcreteAbstractActionStub::class)
        ;

        $container->register(ActionsRegistry::class);
        $container->register(EntityManagerInterface::class);
        $container->register(Environment::class);
        $container->register(RouterInterface::class);
        $container->register(TranslatorInterface::class);

        // Empty config here, we just test definition tags
        $container->setParameter(
            'pierstoval_character_manager.managers',
            $params = [
                'main' => [
                    'character_class' => 'test_abstract',
                    'steps' => [
                        '01' => [
                            'action' => 'steps.default',
                            'label' => '',
                            'onchange_clear' => [],
                            'dependencies' => [],
                        ],
                    ],
                ],
            ]
        );

        yield 'with services' => [$container];

        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass(ConcreteAbstractActionStub::class)
        ;

        $container->register(ActionsRegistry::class);

        $container->register('test_service', \stdClass::class);
        $container->setAlias(EntityManagerInterface::class, 'test_service');
        $container->setAlias(Environment::class, 'test_service');
        $container->setAlias(RouterInterface::class, 'test_service');
        $container->setAlias(TranslatorInterface::class, 'test_service');

        // Empty config here, we just test definition tags
        $container->setParameter('pierstoval_character_manager.managers', $params);

        yield 'with aliases' => [$container];
    }

    /**
     * @dataProvider provide container builder for services injection
     */
    public function test abstract class service definitions injection(ContainerBuilder $container): void
    {
        $stepsPass = new StepsPass();

        $stepsPass->process($container);

        // Test references calls are correct.
        $calls = $container->getDefinition('steps.default')->getMethodCalls();

        static::assertCount(5, $calls);
        static::assertSame('configure', $calls[0][0]);
        static::assertSame('setObjectManager', $calls[1][0]);
        static::assertSame('setTwig', $calls[2][0]);
        static::assertSame('setRouter', $calls[3][0]);
        static::assertSame('setTranslator', $calls[4][0]);
    }

    public function test simple classes are automatically registered as services(): void
    {
        $stepsPass = new StepsPass();

        $container = new ContainerBuilder();

        $container->register(ActionsRegistry::class);
        $container->register(EntityManagerInterface::class);
        $container->register(Environment::class);
        $container->register(RouterInterface::class);
        $container->register(TranslatorInterface::class);

        // Empty config here, we just test definition tags
        $container->setParameter('pierstoval_character_manager.managers', [
            'main' => [
                'character_class' => 'test_abstract',
                'steps' => [
                    '01' => [
                        'action' => ConcreteAbstractActionStub::class,
                        'name' => 'step_1',
                        'label' => 'Step 1',
                        'dependencies' => [],
                        'onchange_clear' => [],
                        'number' => 1,
                    ],
                ],
            ],
        ]);

        $stepsPass->process($container);

        // Test references calls are correct.
        static::assertTrue($container->hasDefinition(ConcreteAbstractActionStub::class));

        $definition = $container->getDefinition(ConcreteAbstractActionStub::class);

        // These should be set by default on every action class not already registered as service
        static::assertTrue($definition->isPrivate());
        static::assertTrue($definition->isAutowired());
        static::assertSame(ConcreteAbstractActionStub::class, $definition->getClass());
    }

    public function test steps order starts from one(): void
    {
        $stepsPass = new StepsPass();
        $container = new ContainerBuilder();

        $container->register(ActionsRegistry::class);

        $inlineStub1 = new TestStub();
        $inlineStub2 = new TestStub();

        $container->setParameter('pierstoval_character_manager.managers', [
            'main' => [
                'character_class' => 'test_abstract',
                'steps' => [
                    '01' => [
                        'action' => ConcreteAbstractActionStub::class,
                        'label' => 'Step 1',
                        'dependencies' => [],
                        'onchange_clear' => [],
                    ],
                    '02' => [
                        'action' => ConcreteAbstractActionStub::class,
                        'label' => 'Step 1',
                        'dependencies' => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
            'another_manager' => [
                'character_class' => 'test_abstract',
                'steps' => [
                    '01' => [
                        'action' => \get_class($inlineStub1),
                        'label' => 'Step 1',
                        'dependencies' => [],
                        'onchange_clear' => [],
                    ],
                    '02' => [
                        'action' => \get_class($inlineStub2),
                        'label' => 'Step 1',
                        'dependencies' => [],
                        'onchange_clear' => [],
                    ],
                ],
            ],
        ]);

        $stepsPass->process($container);

        $managersConfiguration = $container->getParameter('pierstoval_character_manager.managers');

        static::assertSame(1, $managersConfiguration['main']['steps']['01']['number']);
        static::assertSame(2, $managersConfiguration['main']['steps']['02']['number']);
        static::assertSame(1, $managersConfiguration['another_manager']['steps']['01']['number']);
        static::assertSame(2, $managersConfiguration['another_manager']['steps']['02']['number']);
    }
}

class TestStub extends ConcreteAbstractActionStub
{
}
