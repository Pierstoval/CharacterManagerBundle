<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
*/

use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\PierstovalCharacterManagerExtension;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\AbstractTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

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
        $builder = new ContainerBuilder();

        $ext = new PierstovalCharacterManagerExtension(true);

        $ext->load($config, $builder);

        foreach ($expected['pierstoval_character_manager'] as $key => $expectedValue) {
            static::assertEquals($expectedValue, $builder->getParameter('pierstoval_character_manager.'.$key));
        }
    }

    /**
     * @dataProvider provideYamlConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function testYamlConfigurationSymfony2($config, $expected)
    {
        $builder = new ContainerBuilder();

        $ext = new PierstovalCharacterManagerExtension(false);

        $ext->load($config, $builder);

        foreach ($expected['pierstoval_character_manager'] as $key => $expectedValue) {
            static::assertSame($expectedValue, $builder->getParameter('pierstoval_character_manager.'.$key));
        }
    }

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
