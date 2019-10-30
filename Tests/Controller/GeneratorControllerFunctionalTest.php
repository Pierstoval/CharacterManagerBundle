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

use Pierstoval\Tests\WebTestCase as PiersTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GeneratorControllerFunctionalTest extends WebTestCase
{
    use PiersTestCase;

    public function test generate redirects to first step(): void
    {
        $client = $this->getHttpClient();

        $client->getKernel()->boot();

        $client->request('GET', '/generate');

        static::assertSame(302, $client->getResponse()->getStatusCode());
        static::assertSame('/generate/step_01', $client->getResponse()->headers->get('Location'));
    }

    public function test base step route renders correctly(): void
    {
        $client = $this->getHttpClient();

        $client->getKernel()->boot();

        $client->request('GET', '/generate/step_01');

        static::assertSame(200, $client->getResponse()->getStatusCode());
        static::assertSame('Stub response based on abstract class', $client->getResponse()->getContent());
    }

    public function test non existent step route throws 404(): void
    {
        $client = $this->getHttpClient();

        $client->getKernel()->boot();

        $client->request('GET', '/generate/non-existent-step');

        static::assertSame(404, $client->getResponse()->getStatusCode());
    }
}
