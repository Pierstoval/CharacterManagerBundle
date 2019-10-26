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

use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistry;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolver;
use \Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller\AbstractGeneratorControllerTest;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model\StepStub;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StepActionTest extends AbstractGeneratorControllerTest
{
    public function test step action needs session()
    {
        $controller = $this->createController();
        $request = new Request();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Session is mandatory when using the character generator.');

        $controller->stepAction($request, '');
    }

    public function test step action with non existent name()
    {
        $resolver = new StepResolver([
            'manager_one' => $this->createManagerConfiguration('manager_one'),
        ]);

        $controller = $this->createController($resolver);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Step not found.');

        $controller->stepAction($this->createRequest(), 'non_existent_step');
    }

    public function test step action should execute action class()
    {
        $resolver = new StepResolver([
            'test_manager' => $this->createManagerConfiguration('test_manager', [
                'test_step' => [
                    'name' => 'test_step',
                ],
            ]),
        ]);

        $step = StepStub::createStub();

        $action = new ConcreteAbstractActionStub();
        $action->configure($step->getManagerName(), $step->getName(), CharacterStub::class, $resolver);

        $registry = new ActionsRegistry();
        $registry->addStepAction('test_manager', $action->stepName(), $action);

        $response = $this->createController($resolver, null, null, $registry)->stepAction($this->createRequest(), 'test_step');

        static::assertSame('Stub response based on abstract class', $response->getContent());
    }

    public function test step action should check for dependencies()
    {
        $resolver = new StepResolver([
            'test_manager' => $this->createManagerConfiguration('test_manager', [
                'test_step' => [
                    'name' => 'test_step',
                    'label' => 'Test step one'
                ],
                'second_step' => [
                    'name' => 'second_step',
                    'label' => 'Test step two',
                    'number' => 2,
                    'dependencies' => ['test_step'],
                ],
            ]),
        ]);
        $router = $this->createMock(RouterInterface::class);
        $router->expects(self::once())
            ->method('generate')
            ->with('pierstoval_character_generator_index')
            ->willReturn('/generate/manager_one/test_step')
        ;
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects(self::once())
            ->method('trans')
            ->with('steps.dependency_not_set', [
                '%current_step%' => 'Test step two',
                '%dependency%' => 'Test step one',
            ], 'PierstovalCharacterManager')
            ->willReturn('Translated error message')
        ;

        $step2 = $resolver->resolve('second_step');

        $action = new ConcreteAbstractActionStub();
        $action->configure($step2->getManagerName(), $step2->getName(), CharacterStub::class, $resolver);

        $registry = new ActionsRegistry();
        $registry->addStepAction('test_manager', $action->stepName(), $action);

        $request = $this->createRequest();
        $controller = $this->createController($resolver, $router, $translator, $registry);
        $response = $controller->stepAction($request, 'second_step');

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertTrue($response->isRedirect('/generate/manager_one/test_step'));
        static::assertSame(['Translated error message'], $request->getSession()->getFlashBag()->get('error'));
    }
}
