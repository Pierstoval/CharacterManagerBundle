<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CorahnRin\CorahnRinBundle\SheetsManagers;

use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;

interface SheetsManagerInterface
{
    /**
     * @param CharacterInterface $character
     * @param bool               $printer_friendly
     */
    public function generateSheet(CharacterInterface $character, $printer_friendly = false);
}
