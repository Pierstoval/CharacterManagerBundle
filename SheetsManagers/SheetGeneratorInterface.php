<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\SheetsManagers;

use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;

interface SheetGeneratorInterface
{
    /**
     * @param CharacterInterface $character
     * @param bool               $printer_friendly
     *
     * @return string|null Can return the name of the generated file if needed
     */
    public function generateSheet(CharacterInterface $character, bool $printer_friendly = false): ?string;
}
