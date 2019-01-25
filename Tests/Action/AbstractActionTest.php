<?php

/**
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
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolver;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepResolverInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Controller\AbstractGeneratorControllerTest;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model\StepStub;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractActionTest extends AbstractGeneratorControllerTest
{
    /**
     * @dataProvider provideMethodsThatDependOnStepObject
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Step is not defined in current step action. Did you run the "configure()" method?
     */
    public function test any method depending on step object throws exception(string $method, array $arguments = [])
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setRequest($this->createRequest());

        $stub->{$method}(...$arguments);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "stdClass" given.
     */
    public function test configure with wrong character class throws exception()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->configure('', '', \stdClass::class, $this->createMock(StepResolverInterface::class));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "string" given.
     */
    public function test configure with wrong character class type throws exception()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->configure('', '', 'wrong_class_name', $this->createMock(StepResolverInterface::class));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "stdClass" given.
     */
    public function test configure with wrong steps class throws exception()
    {
        $stub = new ConcreteAbstractActionStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve');
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([new \stdClass]);
        $stub->configure('', '', CharacterStub::class, $resolver);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "string" given.
     */
    public function test configure with wrong steps type throws exception()
    {
        $stub = new ConcreteAbstractActionStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve');
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn(['error']);
        $stub->configure('', '', CharacterStub::class, $resolver);
    }

    public function provideMethodsThatDependOnStepObject()
    {
        yield ['updateCharacterStep', ['']];
        yield ['getCharacterProperty', []];
        yield ['nextStep', []];
    }

    public function test next step should increment in session()
    {
        /** @var RouterInterface|MockObject $router */
        $router = $this->createMock(RouterInterface::class);

        $router
            ->expects($this->once())
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
                ]
            ]),
        ]);

        $request = $this->createRequest();

        $stub1 = new ConcreteAbstractActionStub();
        $stub1->configure('test_manager', 'step_1', CharacterStub::class, $resolver);

        $stub1->setRequest($request);
        $stub1->setRouter($router);

        $response = $stub1->nextStep();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertEquals(2, $request->getSession()->get('step.test_manager'));
        static::assertTrue($response->isRedirect('/steps/step_2'));
    }

    public function test getCurrentCharacter with a correct session value()
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturn($stepStub);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['key' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($stub->getCurrentCharacter(), $value);
    }

    public function test getCharacterProperty with no key()
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturn($stepStub);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['test_step' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($value['test_step'], $stub->getCharacterProperty());
    }

    public function test getCharacterProperty with valid key()
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturn($stepStub);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['test_step' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($value['test_step'], $stub->getCharacterProperty('test_step'));
    }

    public function test getCharacterProperty with non existent key()
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub();
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturn($stepStub);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        $stub->setRequest($this->createRequest());

        static::assertNull($stub->getCharacterProperty('non_existent_key'));
    }

    public function test update current step value()
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub(['number' => 2, 'name' => 'step_1']);
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturn($stepStub);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$stepStub]);

        $stub->configure($stepStub->getManagerName(), $stepStub->getName(), CharacterStub::class, $resolver);

        // Prepare request & session
        $request = $this->createRequest();

        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character.test_manager'));
        static::assertSame(2, $request->getSession()->get('step.test_manager'));
    }

    public function test update current step unsets steps that have to be changed()
    {
        $stub = new ConcreteAbstractActionStub();
        $stepStub = StepStub::createStub(['number' => 2, 'name' => 'step_1', 'onchange_clear' => ['step_2', 'step_3']]);
        $resolver = $this->createMock(StepResolverInterface::class);
        $resolver->expects($this->once())->method('resolve')->willReturn($stepStub);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$stepStub]);
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

    public function test goToStep returns RedirectResponse object()
    {
        /** @var RouterInterface|MockObject $router */
        $router = $this->createMock(RouterInterface::class);

        $router
            ->expects($this->once())
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
        $resolver->expects($this->once())->method('resolve')->willReturn($step1);
        $resolver->expects($this->once())->method('getManagerSteps')->willReturn([$step1, $step2, $step3]);
        $stub->setRouter($router);
        $request = $this->createRequest();
        $stub->configure('test_manager', 'step_1', CharacterStub::class, $resolver);
        $stub->setRequest($request);

        $response = $stub->goToStep(3);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertTrue($response->isRedirect('/steps/step_3'));
        static::assertSame(3, $request->getSession()->get('step.test_manager'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Cannot use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction::goToStep if no router is injected in AbstractStepAction.
     */
    public function test goToStep throws exception if no router()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->goToStep(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid step: 25
     */
    public function test goToStep throws exception if step does not exist()
    {
        /** @var RouterInterface|MockObject $router */
        $router = $this->createMock(RouterInterface::class);

        $stub = new ConcreteAbstractActionStub();
        $stub->setRouter($router);
        $stub->goToStep(25);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Request is not set in step action.
     */
    public function test flashMessage should throw exception if no request()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->flashMessage('message');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The session must be available to manage characters. Did you forget to enable the session in the framework?
     */
    public function test flashMessage should throw exception if no session()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setRequest(new Request());
        $stub->flashMessage('message');
    }

    public function test flashMessage with no parameters()
    {
        $stub = new ConcreteAbstractActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        $stub->flashMessage('message');

        static::assertSame(['message'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage removes duplicates()
    {
        $stub = new ConcreteAbstractActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('message');
        $stub->flashMessage('message');

        static::assertSame(['message'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with parameters()
    {
        $stub = new ConcreteAbstractActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('Message with %replacement%', null, ['%replacement%' => 'REPLACEMENT']);

        static::assertSame(['Message with REPLACEMENT'], $stub->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with translator()
    {
        $stub = new ConcreteAbstractActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        $translatorMock = $this->createMock(TranslatorInterface::class);
        $translatorMock->expects($this->once())
            ->method('trans')
            ->with('message')
            ->willReturn('translated')
        ;
        $stub->setTranslator($translatorMock);

        $stub->flashMessage('message');

        static::assertSame(['translated'], $stub->getSession()->getFlashBag()->get('error'));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Request is not set in step action.
     */
    public function test update current step should throw exception if no request()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->updateCharacterStep(null);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Request is not set in step action.
     */
    public function test getCurrentCharacter should throw exception if no request()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->getCurrentCharacter();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The session must be available to manage characters. Did you forget to enable the session in the framework?
     */
    public function test getCurrentCharacter should throw exception if no session()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setRequest(new Request());
        $stub->getCurrentCharacter();
    }

    public function test invalid character class in step action()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "stdClass" given.');

        $stub = new ConcreteAbstractActionStub();
        $stub->configure('', '', \stdClass::class, $this->createMock(StepResolverInterface::class));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The session must be available to manage characters. Did you forget to enable the session in the framework?
     */
    public function test flash message when session is not available()
    {
        $action = new ConcreteAbstractActionStub();
        $action->setRequest(new Request());

        $action->flashMessage('Whatever, it should throw an exception anyway.');
    }
}
