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
 *
 * @runTestsInSeparateProcesses
 */
class ExtensionTest extends AbstractTestCase
{

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Character class must be a valid class extending Pierstoval\Bundle\CharacterManagerBundle\Model\Character. "Inexistent\Class" given.
     */
    public function testInexistentClass()
    {
        $builder = new ContainerBuilder();

        $ext = new PierstovalCharacterManagerExtension(true);

        $ext->load([
            'pierstoval_character_manager' => [
                'character_class' => 'Inexistent\Class',
            ],
        ], $builder);
    }

    /**
     * @dataProvider provideYamlConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function testYamlConfiguration($config, $expected)
    {
        $container = new ContainerBuilder();
        $ext       = new PierstovalCharacterManagerExtension();
        $stepsPass = new StepsPass();

        // Add the default step service
        $container
            ->register('steps.default')
            ->setClass(DefaultStep::class)
        ;

        $ext->load($config, $container);
        $stepsPass->process($container);

        foreach ($expected['pierstoval_character_manager'] as $key => $expectedValue) {
            static::assertSame($expectedValue, $container->getParameter('pierstoval_character_manager.'.$key));
        }
    }

    /**
     * Provide all "extension_test" directory configs and test them through the extension.
     *
     * @return array
     */
    public function provideYamlConfiguration()
    {
        $dir = __DIR__.'/../Fixtures/App/extension_test/';

        $configFiles = glob($dir.'config_*.yml');
        $resultFiles = glob($dir.'result_*.yml');

        sort($configFiles);
        sort($resultFiles);

        $tests = [];

        foreach ($configFiles as $k => $file) {
            $tests[] = [
                Yaml::parse(file_get_contents($file)),
                Yaml::parse(file_get_contents($resultFiles[$k])),
            ];
        }

        return $tests;
    }
}
