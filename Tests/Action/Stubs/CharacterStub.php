<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Action\Stubs;

use Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface;

final class CharacterStub implements CharacterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Stub name';
    }

    /**
     * {@inheritdoc}
     */
    public function getNameSlug()
    {
        return 'stub_name';
    }
}
