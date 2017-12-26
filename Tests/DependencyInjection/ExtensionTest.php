<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler\StepsPass;
use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\PierstovalCharacterManagerExtension;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Test the extension and the compiler pass.
 */
class ExtensionTest extends TestCase
{
    /**
     * @dataProvider provideYamlConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function test yaml configurations($config, $expected)
    {
        $container = new ContainerBuilder();
        $ext       = new PierstovalCharacterManagerExtension();
        $stepsPass = new StepsPass();

        // Add the default step service
        $container
            ->register('steps.default')
            ->setClass(ConcreteAbstractActionStub::class)
        ;

        $ext->load($config, $container);
        $stepsPass->process($container);

        // Sorting the arrays by key name avoid issues with PHP7 and Yaml parsing that sometimes store the keys in the wrong order

        foreach ($expected['pierstoval_character_manager'] as $key => $expectedValue) {
            if (is_array($expectedValue)) {
                ksort($expectedValue);
                if (is_array(current($expectedValue))) {
                    $expectedValue = array_map('ksort', $expectedValue);
                }
            }
            $parameterValue = $container->getParameter($parameter = "pierstoval_character_manager.$key");
            if (is_array($parameterValue)) {
                ksort($parameterValue);
                if (is_array(current($parameterValue))) {
                    $parameterValue = array_map('ksort', $parameterValue);
                }
            }
            static::assertSame($expectedValue, $parameterValue, "$parameter is not the same as expected");
        }
    }

    /**
     * Provide all "extension_test" directory configs and test them through the extension.
     */
    public function provideYamlConfiguration(): \Generator
    {
        $dir = __DIR__.'/../Fixtures/App/extension_test/';

        $configFiles = glob($dir.'config_*.yaml');

        sort($configFiles);

        foreach ($configFiles as $k => $file) {
            $content = Yaml::parse(file_get_contents($file));
            yield basename($file) => [
                $content['input'],
                $content['output'],
            ];
        }
    }
}
