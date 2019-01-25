<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pierstoval\Bundle\CharacterManagerBundle\Entity\Character as BaseCharacter;

/**
 * @ORM\Entity()
 * @ORM\Table(name="character_stubs")
 */
class CharacterStub extends BaseCharacter
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct('Stub characte', 'stub-character');
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
