<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler\StepsPass;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\AbstractTestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\DefaultStep;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\StubStep;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * Test the extension and the compiler pass.
 */
class StepsPassTest extends AbstractTestCase
{
    /**
     * @dataProvider provideNonWorkingConfigurations
     */
    public function testCompilerPassShouldNotWorkIfExtensionNotProcessed(array $config, $expectedException, $expectedExceptionMessage, $stepClass)
    {
        $stepsPass = new StepsPass();

        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass($stepClass)
        ;

        $container->setParameter('pierstoval_character_manager.steps', $config);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $stepsPass->process($container);
    }

    public function provideNonWorkingConfigurations()
    {
        $dir = __DIR__.'/../Fixtures/App/compiler_pass_test/';

        $configFiles = glob($dir.'compiler_config_*.yml');

        sort($configFiles);

        $tests = [];

        foreach ($configFiles as $k => $file) {
            $config = Yaml::parse(file_get_contents($file));
            $tests[] = [
                $config['config'],
                $config['expected_exception'],
                $config['expected_exception_message'],
                $config['step_class'],
            ];
        }

        return $tests;
    }

    public function testAbstractClassServiceDefinitions()
    {
        $stepsPass = new StepsPass();

        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass(DefaultStep::class)
            ->addTag('pierstoval_character_step')
        ;

        // Empty config here, we just test definition tags
        $container->setParameter('pierstoval_character_manager.steps', []);
        $container->setParameter('pierstoval_character_manager.character_class', 'test_abstract');

        $stepsPass->process($container);

        // Test references calls are correct.
        // First (index 0) should be setDefaultServices with 4 services as arguments.
        // Second (index 1) should be setCharacterClass with one string argument.

        $definition = $container->getDefinition('steps.default');

        $calls = $definition->getMethodCalls();

        static::assertCount(2, $calls);

        // Test index 1 (faster)
        static::assertSame(['setCharacterClass', ['test_abstract']], $calls[1]);

        $validCallsReferences = [
            'doctrine.orm.entity_manager',
            'templating',
            'router',
            'translator',
        ];

        static::assertSame('setDefaultServices', $calls[0][0]);
        static::assertCount(4, $calls[0][1]);

        /** @var Reference $callArgument */
        foreach ($calls[0][1] as $callArgument) {
            static::assertInstanceOf(Reference::class, $callArgument);
            $referenceId = (string) $callArgument;

            // The service id should exist in the valid ones set above.
            $indexOfReference = array_search($referenceId, $validCallsReferences, true);
            static::assertNotFalse($indexOfReference);

            // Remove any correctly asserted so we can check they were all in the list.
            unset($validCallsReferences[$indexOfReference]);
        }

        // There should be no more valid reference as they should've been unset by foreach loop.
        static::assertCount(0, $validCallsReferences);
    }

    public function testStubClassServiceDefinitions()
    {
        $stepsPass = new StepsPass();

        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass(StubStep::class)
            ->addTag('pierstoval_character_step')
        ;

        // Empty config here, we just test definition tags
        $container->setParameter('pierstoval_character_manager.steps', []);
        $container->setParameter('pierstoval_character_manager.character_class', 'test_stub');

        $stepsPass->process($container);

        $definition = $container->getDefinition('steps.default');

        $calls = $definition->getMethodCalls();

        static::assertCount(1, $calls);
        static::assertSame(['setCharacterClass', ['test_stub']], $calls[0]);
    }
}
