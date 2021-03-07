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

use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\Compiler\StepsPass;
use Pierstoval\Bundle\CharacterManagerBundle\DependencyInjection\PierstovalCharacterManagerExtension;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
    public function test yaml configurations($config, $expected): void
    {
        $container = new ContainerBuilder();
        $ext = new PierstovalCharacterManagerExtension();
        $stepsPass = new StepsPass();

        // Add the default step service
        $container
            ->register('steps.default')
            ->setClass(ConcreteAbstractActionStub::class)
        ;

        $ext->load($config, $container);
        $stepsPass->process($container);

        foreach ($expected['pierstoval_character_manager'] as $key => $expectedValue) {
            $parameterValue = $container->getParameter($parameter = "pierstoval_character_manager.{$key}");
            static::assertSame($expectedValue, $parameterValue, "{$parameter} is not the same as expected");
        }
    }

    /**
     * Provide all "extension_test" directory configs and test them through the extension.
     */
    public function provideYamlConfiguration(): Generator
    {
        $dir = \dirname(__DIR__).'/Fixtures/App/extension_test/';

        $configFiles = \glob($dir.'config_*.php');

        \sort($configFiles, \SORT_NATURAL);

        foreach ($configFiles as $file) {
            $content = require $file;
            yield \basename($file) => [
                $content['input'],
                $content['output'],
            ];
        }
    }
}
