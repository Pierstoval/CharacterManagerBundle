<?php

namespace CorahnRin\CorahnRinBundle\SheetsManagers;

use Pierstoval\Bundle\CharacterManagerBundle\Model\Character;

interface SheetsManagerInterface
{

    /**
     * @param Character  $character
     * @param bool       $printer_friendly
     */
    public function generateSheet(Character $character, $printer_friendly = false);

}
