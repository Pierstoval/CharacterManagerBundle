<?php

declare(strict_types=1);

/*
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class IndexActionTest extends AbstractGeneratorControllerTest
{
    public function test index needs session(): void
    {
        $controller = $this->createController();
        $request = new Request();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Session is mandatory when using the character generator.');

        $controller->indexAction($request);
    }

    public function test index with no configuration returns 404(): void
    {
        $stepsResolver = new StepResolver(['manager' => ['steps' => []]]);

        $controller = $this->createController($stepsResolver);
        $request = $this->createRequest();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('No step found to start the generator.');

        $controller->indexAction($request);
    }

    public function test index with no step in session redirects to first step(): void
    {
        $resolver = new StepResolver([
            'manager_one' => $this->createManagerConfiguration('manager_one'),
        ]);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->with('pierstoval_character_generator_step', ['requestStep' => '01'])
            ->willReturn('/generate/01')
        ;

        $controller = $this->createController($resolver, $router);
        $request = $this->createRequest();

        $response = $controller->indexAction($request);

        static::assertSame('/generate/01', $response->headers->get('Location'));
    }

    public function test index with manager in session redirects to first step with manager name(): void
    {
        $resolver = new StepResolver([
            'manager_one' => $this->createManagerConfiguration('manager_one'),
        ]);

        $router = $this->createMock(RouterInterface::class);
        $router->expects(static::once())
            ->method('generate')
            ->with('pierstoval_character_generator_step', ['requestStep' => '01', 'manager' => 'manager_one'])
            ->willReturn('/generate/manager_one/01')
        ;

        $controller = $this->createController($resolver, $router);
        $request = $this->createRequest();

        $response = $controller->indexAction($request, 'manager_one');

        static::assertSame('/generate/manager_one/01', $response->headers->get('Location'));
    }
}
