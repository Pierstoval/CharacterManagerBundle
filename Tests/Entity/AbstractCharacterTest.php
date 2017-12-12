<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;

class AbstractCharacterTest extends TestCase
{
    public function test public getters return types()
    {
        $character = new CharacterStub();

        static::assertInternalType('string', $character->getName());
        static::assertInternalType('string', $character->getNameSlug());
    }
}
