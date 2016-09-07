<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures;

use Tests\WebTestCase;

/**
 * Class AbstractTestCase.
 */
class AbstractTestCase extends WebTestCase
{
    protected static function getKernelClass() {
        return 'Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\App\AppKernel';
    }
}
