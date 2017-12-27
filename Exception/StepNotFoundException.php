<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Exception;

class StepNotFoundException extends \RuntimeException
{
    /**
     * @param int|string $step
     */
    public function __construct($step, string $managerName)
    {
        parent::__construct("\"$step\" step does not exist in manager $managerName.");
    }
}
