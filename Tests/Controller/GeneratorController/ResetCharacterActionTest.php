<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller\GeneratorController;

use Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller\AbstractGeneratorControllerTest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ResetCharacterActionTest extends AbstractGeneratorControllerTest
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Session is mandatory when using the character generator.
     */
    public function test reset needs session()
    {
        $controller = $this->createController();
        $request = new Request();

        $controller->resetCharacterAction($request);
    }

    public function test reset session and flash message()
    {
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('pierstoval_character_generator_index')
            ->willReturn('/generate/')
        ;
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('steps.reset.character', [], 'PierstovalCharacterManager')
            ->willReturn('Translated flash message')
        ;

        $controller = $this->createController(null, $router, $translator);
        $request = $this->createRequest();
        $session = $request->getSession();

        if (!$session) {
            throw new \RuntimeException('Session should have been set in the test.');
        }

        $session->set('step', 10);
        $session->set('character', ['01' => 'step value is set and has to be removed']);

        $response = $controller->resetCharacterAction($request);

        static::assertSame([], $session->get('character'));
        static::assertSame(1, $session->get('step'));
        static::assertSame(['Translated flash message'], $session->getFlashBag()->get('success'));
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame('/generate/', $response->headers->get('location'));
    }
}
