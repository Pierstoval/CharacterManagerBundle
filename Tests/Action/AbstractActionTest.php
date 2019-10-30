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

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Action;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolver;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller\AbstractGeneratorControllerTest;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model\StepStub;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractActionTest extends AbstractGeneratorControllerTest
{
    /**
     * @dataProvider provideMethodsThatDependOnStepObject
     */
    public function test any method depending on step object throws exception(string $method, array $arguments = []): void
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setRequest($this->createRequest());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Step action is not configured. Did you forget to run the "configure()" method?');

        $stub->{$method}(...$arguments);
    }

    public function test configure with wrong character class throws exception(): void
    {
        $stub = new ConcreteAbstractActionStub();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "stdClass" given.');

        $stub->configure('', '', \stdClass::class, $this->createMock(StepResolverInterface::class));
    }

    public function test configure with wrong character class type throws exception(): void
    {
        $stub = new ConcreteAbstractActionStub();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "string" given.');

        $stub->configure('', '', 'wrong_class_name', $this->createMock(StepResolverInterface::class));
    }

    public function test configure with wrong steps class throws exception(): void
    {
        $stub = new ConcreteAbstractActionStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects(static::once())->method('resolve');
        $resolver->expects(static::once())->method('getManagerSteps')->willReturn([new \stdClass()]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "stdClass" given.');

        $stub->configure('', '', CharacterStub::class, $resolver);
    }

    public function test configure with wrong steps type throws exception(): void
    {
        $stub = new ConcreteAbstractActionStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects(static::once())->method('resolve');
        $resolver->expects(static::once())->method('getManagerSteps')->willReturn(['error']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "string" given.');

        $stub->configure('', '', CharacterStub::class, $resolver);
    }

    public function test configure an already configured action throws exception(): void
    {
        $stub = new ConcreteAbstractActionStub();

        $stub->configure('', '', CharacterStub::class, $this->createMock(StepResolverInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot reconfigure an already configured step action.');

        $stub->configure('', '', CharacterStub::class, $this->createMock(StepResolverInterface::class));
    }

    public function provideMethodsThatDependOnStepObject()
    {
        yield ['updateCharacterStep', ['']];
        yield ['getCharacterProperty', []];
        yield ['nextStep', []];
    }

    public function test next step should increment in session(): void
    {
        /** @var MockObject|RouterInterface $router */
        $router = $this->createMock(RouterInterface::class);

        $router
            ->expects(static::once())
            ->method('generate')
            ->with('pierstoval_character_generator_step', ['requestStep' => 'step_2'])
            ->willReturn('/steps/step_2')
        ;

        $resolver = new StepResolver([
            'test_manager' => $this->createManagerConfiguration('test_manager', [
                'step_1' => [
                    'number' => 1,
                ],
                'step_2' => [
                    'number' => 2,
                ],
            ]),
        ]);

        $request = $this->createRequest();

        $stub1 = new ConcreteAbstractActionStub();
        $stub1->configure('test_manager', 'step_1', CharacterStub::class, $resolver);

        $stub1->setRequest($request);
        $stub1->setRouter($router);

        $response = $stub1->nextStep();

        static::assertEquals(2, $request->getSession()->get('step.test_manager'));
        static::assertTrue($response->isRedirect('/steps/step_2'));
    }

    public function test getCurrentCharacter with a correct session value(): void
    {
        $stub = $this->getDefaultStub();

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['key' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($stub->getCurrentCharacter(), $value);
    }

    public function test getCharacterProperty with no key(): void
    {
        $stub = $this->getDefaultStub();

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['test_step' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($value['test_step'], $stub->getCharacterProperty());
    }

    public function test getCharacterProperty with valid key(): void
    {
        $stub = $this->getDefaultStub();

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['test_step' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($value['test_step'], $stub->getCharacterProperty('test_step'));
    }

    public function test getCharacterProperty with non existent key(): void
    {
        $stub = $this->getDefaultStub();

        $stub->setRequest($this->createRequest());

        static::assertNull($stub->getCharacterProperty('non_existent_key'));
    }

    public function test update current step value(): void
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub(['number' => 2, 'name' => 'step_1']);
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects(static::once())->method('resolve')->willReturn($stepStub);
        $resolver->expects(static::once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        // Prepare request & session
        $request = $this->createRequest();

        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character.test_manager'));
        static::assertSame(2, $request->getSession()->get('step.test_manager'));
    }

    public function test update current step unsets steps that have to be changed(): void
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub(['number' => 2, 'name' => 'step_1', 'onchange_clear' => ['step_2', 'step_3']]);
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects(static::once())->method('resolve')->willReturn($stepStub);
        $resolver->expects(static::once())->method('getManagerSteps')->willReturn([$stepStub]);
        $step = new Step(2, 'step_1', \get_class($stub), 'step_1', 'main_manager', ['step_2', 'step_3'], []);

        // Prepare request & session
        $request = $this->createRequest();

        // Add "onchange" steps in session
        $request->getSession()->set('character.test_manager', ['step_2' => true, 'step_3' => true]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);
        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character.test_manager'));
        static::assertSame($step->getNumber(), $request->getSession()->get('step.test_manager'));
        static::assertArrayNotHasKey('step_2', $request->getSession()->get('character.test_manager'));
        static::assertArrayNotHasKey('step_3', $request->getSession()->get('character.test_manager'));
    }

    public function test goToStep returns RedirectResponse object(): void
    {
        /** @var MockObject|RouterInterface $router */
        $router = $this->createMock(RouterInterface::class);

        $router
            ->expects(static::once())
            ->method('generate')
            ->with('pierstoval_character_generator_step', ['requestStep' => 'step_3'])
            ->willReturn('/steps/step_3')
        ;

        // Prepare all stubs for testing
        $stub = new ConcreteAbstractActionStub();
        $step1 = new Step(1, 'step_1', \get_class($stub), 'step_1', 'test_manager', [], []);
        $step2 = new Step(2, 'step_2', \get_class($stub), 'step_2', 'test_manager', [], []);
        $step3 = new Step(3, 'step_3', \get_class($stub), 'step_3', 'test_manager', [], []);
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects(static::once())->method('resolve')->willReturn($step1);
        $resolver->expects(static::once())->method('getManagerSteps')->willReturn([$step1, $step2, $step3]);
        $stub->setRouter($router);
        $request = $this->createRequest();
        $stub->configure('test_manager', 'step_1', CharacterStub::class, $resolver);
        $stub->setRequest($request);

        $response = $stub->goToStep(3);

        static::assertTrue($response->isRedirect('/steps/step_3'));
        static::assertSame(3, $request->getSession()->get('step.test_manager'));
    }

    public function test goToStep throws exception if no router(): void
    {
        $stub = $this->getDefaultConfiguredAction();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction::goToStep if no router is injected in AbstractStepAction.');

        $stub->goToStep(1);
    }

    public function test goToStep throws exception if not configured(): void
    {
        $stub = new ConcreteAbstractActionStub();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Step action is not configured. Did you forget to run the "configure()" method?');

        $stub->goToStep(1);
    }

    public function test goToStep throws exception if step does not exist(): void
    {
        /** @var MockObject|RouterInterface $router */
        $router = $this->createMock(RouterInterface::class);

        $stub = $this->getDefaultConfiguredAction();
        $stub->setRouter($router);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid step: 25');

        $stub->goToStep(25);
    }

    public function test flashMessage should throw exception if no request(): void
    {
        $stub = $this->getDefaultConfiguredAction();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request is not set in step action.');

        $stub->flashMessage('message');
    }

    public function test flashMessage should throw exception if no session(): void
    {
        $stub = $this->getDefaultConfiguredAction();
        $stub->setRequest(new Request());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The session must be available to manage characters. Did you forget to enable the session in the framework?');

        $stub->flashMessage('message');
    }

    public function test flashMessage with no parameters(): void
    {
        $stub = $this->getDefaultConfiguredAction();

        $request = $this->createRequest();
        $stub->setRequest($request);

        $stub->flashMessage('message');

        static::assertSame(['message'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage removes duplicates(): void
    {
        $stub = $this->getDefaultConfiguredAction();

        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('message');
        $stub->flashMessage('message');

        static::assertSame(['message'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with parameters(): void
    {
        $stub = $this->getDefaultConfiguredAction();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('Message with %replacement%', null, ['%replacement%' => 'REPLACEMENT']);

        static::assertSame(['Message with REPLACEMENT'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with translator(): void
    {
        $stub = $this->getDefaultConfiguredAction();
        $request = $this->createRequest();
        $stub->setRequest($request);

        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock->expects(static::once())
            ->method('trans')
            ->with('message')
            ->willReturn('translated')
        ;
        $stub->setTranslator($translatorMock);

        $stub->flashMessage('message');

        static::assertSame(['translated'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test update current step should throw exception if no request(): void
    {
        $stub = $this->getDefaultConfiguredAction();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request is not set in step action.');

        $stub->updateCharacterStep(null);
    }

    public function test getCurrentCharacter should throw exception if no request(): void
    {
        $stub = $this->getDefaultConfiguredAction();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request is not set in step action.');

        $stub->getCurrentCharacter();
    }

    public function test getCurrentCharacter should throw exception if no session(): void
    {
        $stub = $this->getDefaultConfiguredAction();
        $stub->setRequest(new Request());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The session must be available to manage characters. Did you forget to enable the session in the framework?');

        $stub->getCurrentCharacter();
    }

    public function test invalid character class in step action(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "stdClass" given.');

        $stub = new ConcreteAbstractActionStub();
        $stub->configure('', '', \stdClass::class, $this->createMock(StepResolverInterface::class));
    }

    public function test flash message when session is not available(): void
    {
        $action = $this->getDefaultConfiguredAction();
        $action->setRequest(new Request());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The session must be available to manage characters. Did you forget to enable the session in the framework?');

        $action->flashMessage('Whatever, it should throw an exception anyway.');
    }

    private function getDefaultStub(): ConcreteAbstractActionStub
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects(static::once())->method('resolve')->willReturn($stepStub);
        $resolver->expects(static::once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        return $stub;
    }

    private function getDefaultResolver(): StepResolverInterface
    {
        return new class() implements StepResolverInterface {
            public function resolve(string $stepName, string $managerName = null): StepInterface
            {
                return StepStub::createStub(['name' => $stepName, 'manager_name' => $managerName]);
            }

            public function resolveNumber(int $stepNumber, string $managerName = null): StepInterface
            {
                return StepStub::createStub(['number' => $stepNumber, 'manager_name' => $managerName]);
            }

            public function getManagerSteps(string $managerName = null): array
            {
                return [StepStub::createStub(['manager_name' => $managerName])];
            }

            public function resolveManagerName(string $managerName = null): string
            {
                return $managerName ?: 'test_manager';
            }
        };
    }

    private function getDefaultConfiguredAction(): ConcreteAbstractActionStub
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->configure('test_manager', 'test_step', CharacterStub::class, $this->getDefaultResolver());

        return $stub;
    }
}
