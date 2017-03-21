<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Action;

use Pierstoval\Bundle\CharacterManagerBundle\Tests\Action\Stubs\IncrementActionStub;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ActionTest extends AbstractActionTestCase
{
    public function test next step should increment in session()
    {
        $step = $this->createStepAction(IncrementActionStub::class, null, ['redirect_path' => '/step_2']);

        $request = $this->createRequest();

        $step->setRequest($request);

        $response = $step->execute();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertEquals(2, $request->getSession()->get('step'));
        static::assertTrue($response->isRedirect('/step_2'));
    }
}
