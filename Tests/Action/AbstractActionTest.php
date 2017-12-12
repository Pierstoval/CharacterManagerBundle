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

use Doctrine\Common\Persistence\ObjectManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Action\ConcreteAbstractActionStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Entity\CharacterStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\Stubs\Model\StepStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\RequestTestTrait;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;

class AbstractActionTest extends TestCase
{
    use RequestTestTrait;

    /**
     * @dataProvider provideMethodsThatDependOnStepObject
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage To get current step you need to use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction:setStep method and inject a StepInterface instance.
     */
    public function test any method depending on step object throws exception(string $method, array $arguments = [])
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setRequest($this->createRequest());

        $stub->{$method}(...$arguments);
    }

    public function provideMethodsThatDependOnStepObject()
    {
        yield ['updateCharacterStep', ['']];
        yield ['getCurrentCharacter', []];
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

        $stub = new ConcreteAbstractActionStub();

        $step1 = new Step(1, 'step_1', \get_class($stub), 'step_1', 'main_manager', [], []);
        $step2 = new Step(2, 'step_2', \get_class($stub), 'step_2', 'main_manager', [], []);
        $stub->setStep($step1);
        $stub->setSteps([$step1, $step2]);
        $request = $this->createRequest();
        $stub->setRequest($request);
        $stub->setRouter($router);

        $response = $stub->nextStep();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertEquals(2, $request->getSession()->get('step.main_manager'));
        static::assertTrue($response->isRedirect('/steps/step_2'));
    }

    public function test getCurrentCharacter with a correct session value()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setStep(StepStub::createStub());

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['key' => 'value'];
        $request->getSession()->set('character.test_manager', $value);

        $stub->setRequest($request);

        static::assertSame($stub->getCurrentCharacter(), $value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage To get current step you need to use Pierstoval\Bundle\CharacterManagerBundle\Action\AbstractStepAction:setStep method and inject a StepInterface instance.
     */
    public function test getCharacterProperty should throw exception if no step configured()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->getCharacterProperty();
    }

    public function test getCharacterProperty with no key()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setStep(StepStub::createStub());

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
        $stub->setStep(StepStub::createStub());

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
        $stub->setStep(StepStub::createStub());
        $stub->setRequest($this->createRequest());

        static::assertNull($stub->getCharacterProperty('non_existent_key'));
    }

    public function test update current step value()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setStep(new Step(2, 'step_1', \get_class($stub), 'step_1', 'main_manager', [], []));

        // Prepare request & session
        $request = $this->createRequest();

        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character.main_manager'));
        static::assertSame(2, $request->getSession()->get('step.main_manager'));
    }

    public function test update current step unsets steps that have to be changed()
    {
        $stub = new ConcreteAbstractActionStub();
        $step = new Step(2, 'step_1', \get_class($stub), 'step_1', 'main_manager', ['step_2', 'step_3'], []);
        $stub->setStep($step);

        // Prepare request & session
        $request = $this->createRequest();

        // Add "onchange" steps in session
        $request->getSession()->set('character.main_manager', ['step_2' => true, 'step_3' => true]);

        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character.main_manager'));
        static::assertSame($step->getNumber(), $request->getSession()->get('step.main_manager'));
        static::assertArrayNotHasKey('step_2', $request->getSession()->get('character.main_manager'));
        static::assertArrayNotHasKey('step_3', $request->getSession()->get('character.main_manager'));
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
        $step1 = new Step(1, 'step_1', \get_class($stub), 'step_1', 'main_manager', [], []);
        $step2 = new Step(2, 'step_2', \get_class($stub), 'step_2', 'main_manager', [], []);
        $step3 = new Step(3, 'step_3', \get_class($stub), 'step_3', 'main_manager', [], []);
        $stub->setStep($step1);
        $stub->setSteps([$step1, $step2, $step3]);
        $stub->setRouter($router);
        $request = $this->createRequest();
        $stub->setRequest($request);

        $response = $stub->goToStep(3);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertTrue($response->isRedirect('/steps/step_3'));
        static::assertSame(3, $request->getSession()->get('step.main_manager'));
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

        static::assertSame(['message'], $request->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage removes duplicates()
    {
        $stub = new ConcreteAbstractActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('message');
        $stub->flashMessage('message');

        static::assertSame(['message'], $request->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with parameters()
    {
        $stub = new ConcreteAbstractActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('Message with %replacement%', null, ['%replacement%' => 'REPLACEMENT']);

        static::assertSame(['Message with REPLACEMENT'], $request->getSession()->getFlashBag()->get('error'));
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

        static::assertSame(['translated'], $request->getSession()->getFlashBag()->get('error'));
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
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "integer" given.
     */
    public function test setSteps should throw exception with scalar arguments()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setSteps(range(1, 2));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "stdClass" given.
     */
    public function test setSteps should throw exception with wrong object arguments()
    {
        $stub = new ConcreteAbstractActionStub();
        $stub->setSteps([new \stdClass()]);
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

    public function test getters and setters()
    {
        $stub = new ConcreteAbstractActionStub();
        $steps = [new Step(1, 'step_1', \get_class($stub), 'step_1', 'main_manager', [], [])];

        // Just to make sure setters don't fail
        $stub->setObjectManager($this->createMock(ObjectManager::class));
        $stub->setTwig($this->createMock(Environment::class));
        $stub->setTranslator($this->createMock(TranslatorInterface::class));
        $stub->setRouter($this->createMock(RouterInterface::class));

        $stub->setSteps($steps);
        $stub->setStep($steps[0]);
        static::assertSame($steps[0], $stub->getStep());

        $stub->setCharacterClass(CharacterStub::class);

        static::assertSame(CharacterStub::class, $stub->getCharacterClass());
        static::assertSame($steps, $stub->getSteps());
    }

    public function test invalid character class in step action()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "stdClass" given.');

        $stub = new ConcreteAbstractActionStub();
        $stub->setCharacterClass(\stdClass::class);
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
