<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Model;

interface CharacterInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getNameSlug();
}
