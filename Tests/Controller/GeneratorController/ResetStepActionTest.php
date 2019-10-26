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

use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolver;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller\AbstractGeneratorControllerTest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResetStepActionTest extends AbstractGeneratorControllerTest
{
    public function test reset step needs session()
    {
        $controller = $this->createController();
        $request = new Request();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Session is mandatory when using the character generator.');

        $controller->resetStepAction($request, '');
    }

    public function test reset step with non existent name()
    {
        $resolver = new StepResolver([
            'manager_one' => $this->createManagerConfiguration('manager_one'),
        ]);

        $controller = $this->createController($resolver);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Step not found.');

        $controller->resetStepAction($this->createRequest(), 'non_existent_step');
    }

    public function test reset step removes onchange clear steps()
    {
        $resolver = new StepResolver([
            'manager_one' => $this->createManagerConfiguration('manager_one', [
                '01' => [
                    'name'           => '01',
                    'onchange_clear' => ['03'],
                ],
                '02' => [
                    'name' => '02',
                ],
                '03' => [
                    'name' => '03',
                ],
            ]),
        ]);
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('pierstoval_character_generator_step', ['requestStep' => '01'])
            ->willReturn('/generate/01')
        ;
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('steps.reset.step', [], 'PierstovalCharacterManager')
            ->willReturn('Translated flash message')
        ;

        $controller = $this->createController($resolver, $router, $translator);
        $request = $this->createRequest();
        /** @var Session $session */
        $session = $request->getSession();

        $session->set('character.manager_one', [
            '01' => 'Should be removed',
            '02' => 'Should be kept',
            '03' => 'Should be removed',
        ]);

        $response = $controller->resetStepAction($request, '01');

        static::assertTrue($response->isRedirect('/generate/01'));
        static::assertSame([
            '02' => 'Should be kept'
        ], $session->get('character.manager_one'));
        static::assertSame(['Translated flash message'], $session->getFlashBag()->get('success'));
    }

    public function test reset step with multiple managers correctly redirects()
    {
        $resolver = new StepResolver([
            'manager_one' => $this->createManagerConfiguration('manager_one', [
                '01' => [
                    'name' => '01',
                ],
            ]),
            'manager_two' => $this->createManagerConfiguration('manager_two', [
                '01' => [
                    'name' => '01',
                ],
            ]),
        ]);
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('pierstoval_character_generator_step', ['requestStep' => '01', 'manager' => 'manager_two'])
            ->willReturn('/generate/manager_two/01')
        ;

        $response = $this->createController($resolver, $router)->resetStepAction($this->createRequest(), '01', 'manager_two');

        static::assertTrue($response->isRedirect('/generate/manager_two/01'));
    }
}
