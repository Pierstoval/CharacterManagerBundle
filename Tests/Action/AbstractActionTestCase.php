<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Action;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

abstract class AbstractActionTestCase extends TestCase
{
    /**
     * @return Request
     */
    public function createRequest()
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }
}
