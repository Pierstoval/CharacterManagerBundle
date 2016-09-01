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
use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\PierstovalCharacterManagerExtension;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\AbstractTestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Action\DefaultStep;
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
}
