<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler\StepsPass;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\AbstractTestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\DefaultTestStep;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\StubStep;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Test the extension and the compiler pass.
 */
class StepsPassTest extends AbstractTestCase
{
    /**
     * @dataProvider provideNonWorkingConfigurations
     */
    public function test compiler pass should not work if extension not processed(array $config, $expectedException, $expectedExceptionMessage, $stepClass)
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

    public function test abstract class service definitions()
    {
        $stepsPass = new StepsPass();

        $container = new ContainerBuilder();
        $container
            ->register('steps.default')
            ->setClass(DefaultTestStep::class)
            ->addTag('pierstoval_character_step')
        ;

        $container->register('doctrine.orm.entity_manager');
        $container->register('templating');
        $container->register('router');
        $container->register('translator');

        // Empty config here, we just test definition tags
        $container->setParameter('pierstoval_character_manager.steps', []);
        $container->setParameter('pierstoval_character_manager.character_class', 'test_abstract');

        $stepsPass->process($container);

        // Test references calls are correct.
        $definition = $container->getDefinition('steps.default');

        $calls = $definition->getMethodCalls();

        static::assertCount(5, $calls);
        static::assertSame('doctrine.orm.entity_manager', $calls[0][0]);
        static::assertSame('templating', $calls[1][0]);
        static::assertSame('router', $calls[2][0]);
        static::assertSame('translator', $calls[3][0]);
        static::assertSame(['setCharacterClass', ['test_abstract']], $calls[4]);
    }

    public function provideNonWorkingConfigurations()
    {
        $dir = __DIR__.'/../Fixtures/App/compiler_pass_test/';

        $configFiles = glob($dir.'compiler_config_*.yml');

        sort($configFiles);

        $tests = [];

        foreach ($configFiles as $k => $file) {
            $config = Yaml::parse(file_get_contents($file));
            $tests[basename($file)] = [
                $config['config'],
                $config['expected_exception'],
                $config['expected_exception_message'],
                $config['step_class'],
            ];
        }

        return $tests;
    }

    public function test stub class service definitions()
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
