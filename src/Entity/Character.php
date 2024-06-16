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

namespace Pierstoval\Bundle\CharacterManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;

abstract class Character implements CharacterInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    #[ORM\Column(name: "name", type: "string", length: 255, nullable: false)]
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="name_slug", type="string", length=255, nullable=false)
     */
    #[ORM\Column(name: "name_slug", type: "string", length: 255, nullable: false)]
    protected $nameSlug;

    public function __construct(string $name, string $nameSlug)
    {
        $this->name = $name;
        $this->nameSlug = $nameSlug;
    }

    public function getName(): string
    {
        return $this->name ?: '';
    }

    public function getNameSlug(): string
    {
        return $this->nameSlug ?: '';
    }
}
