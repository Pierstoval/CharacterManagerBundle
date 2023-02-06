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

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class MultipleManagersGeneratorControllerFunctionalTest extends WebTestCase
{
    public function test main generate redirects to first step(): void
    {
        $client = static::createClient();

        $client->getKernel()->boot();

        $client->request('GET', '/main/generate');

        static::assertSame(302, $client->getResponse()->getStatusCode());
        static::assertSame('/main/generate/step_01', $client->getResponse()->headers->get('Location'));
    }

    public function test other generate redirects to first step(): void
    {
        $client = static::createClient();

        $client->getKernel()->boot();

        $client->request('GET', '/other/generate');

        static::assertSame(302, $client->getResponse()->getStatusCode());
        static::assertSame('/other/generate/step_01', $client->getResponse()->headers->get('Location'));
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        $options['environment'] = 'test_more_managers';

        return parent::createKernel($options);
    }
}
