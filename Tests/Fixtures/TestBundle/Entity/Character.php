<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Character as BaseCharacter;

/**
 * @ORM\Entity()
 * @ORM\Table(name="pierstoval_character_manager")
 */
class Character extends BaseCharacter
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $data
     *
     * @return BaseCharacter
     */
    public function createFromGenerator(array $data)
    {
        // TODO: Implement createFromGenerator() method.
    }
}
