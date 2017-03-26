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

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pierstoval\Bundle\CharacterManagerBundle\Model\Step;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Action\Stubs\ActionStub;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\TestBundle\Entity\CharacterStub;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

final class BaseActionTest extends AbstractActionTestCase
{
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

        $stub = new ActionStub();

        $step1 = new Step(1, 'step_1', get_class($stub), 'step_1', [], []);
        $step2 = new Step(2, 'step_2', get_class($stub), 'step_2', [], []);
        $stub->setStep($step1);
        $stub->setSteps([$step1, $step2]);
        $request = $this->createRequest();
        $stub->setRequest($request);
        $stub->setRouter($router);

        $response = $stub->nextStep();

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertEquals(2, $request->getSession()->get('step'));
        static::assertTrue($response->isRedirect('/steps/step_2'));
    }

    public function test getCurrentCharacter with a correct session value()
    {
        $stub = new ActionStub();

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['key' => 'value'];
        $request->getSession()->set('character', $value);

        $stub->setRequest($request);

        static::assertSame($stub->getCurrentCharacter(), $value);
    }

    public function test getCharacterProperty should throw exception if no step configured()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('To get current step you need to use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction:setStep method and inject a Step instance.');

        $stub = new ActionStub();
        $stub->getCharacterProperty();
    }

    public function test getCharacterProperty with no key()
    {
        $stub = new ActionStub();
        $stub->setStep(new Step(1, 'step_1', get_class($stub), 'step_1', [], []));

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['step_1' => 'value'];
        $request->getSession()->set('character', $value);

        $stub->setRequest($request);

        static::assertSame($value['step_1'], $stub->getCharacterProperty());
    }

    public function test getCharacterProperty with valid key()
    {
        $stub = new ActionStub();

        // Prepare request & session
        $request = $this->createRequest();
        $value = ['step_1' => 'value'];
        $request->getSession()->set('character', $value);

        $stub->setRequest($request);

        static::assertSame($value['step_1'], $stub->getCharacterProperty('step_1'));
    }

    public function test getCharacterProperty with inexistent key()
    {
        $stub = new ActionStub();

        // Prepare request & session
        $stub->setRequest($this->createRequest());

        static::assertNull($stub->getCharacterProperty('inexistent_key'));
    }

    public function test update current step value()
    {
        $stub = new ActionStub();
        $stub->setStep(new Step(2, 'step_1', get_class($stub), 'step_1', [], []));

        // Prepare request & session
        $request = $this->createRequest();

        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character'));
        static::assertSame(2, $request->getSession()->get('step'));
    }

    public function test update current step unsets steps that have to be changed()
    {
        $stub = new ActionStub();
        $step = new Step(2, 'step_1', get_class($stub), 'step_1', ['step_2', 'step_3'], []);
        $stub->setStep($step);

        // Prepare request & session
        $request = $this->createRequest();

        // Add "onchange" steps in session
        $request->getSession()->set('character', ['step_2' => true, 'step_3' => true]);

        $stub->setRequest($request);

        $stub->updateCharacterStep('new_value');

        static::assertSame(['step_1' => 'new_value'], $request->getSession()->get('character'));
        static::assertSame($step->getStep(), $request->getSession()->get('step'));
        static::assertArrayNotHasKey('step_2', $request->getSession()->get('character'));
        static::assertArrayNotHasKey('step_3', $request->getSession()->get('character'));
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
        $stub = new ActionStub();
        $step1 = new Step(1, 'step_1', get_class($stub), 'step_1', [], []);
        $step2 = new Step(2, 'step_2', get_class($stub), 'step_2', [], []);
        $step3 = new Step(3, 'step_3', get_class($stub), 'step_3', [], []);
        $stub->setStep($step1);
        $stub->setSteps([$step1, $step2, $step3]);
        $stub->setRouter($router);
        $request = $this->createRequest();
        $stub->setRequest($request);

        $response = $stub->goToStep(3);

        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertTrue($response->isRedirect('/steps/step_3'));
        static::assertSame(3, $request->getSession()->get('step'));
    }

    public function test goToStep throws exception if no router()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction::goToStep if no router is injected in StepAction.');

        $stub = new ActionStub();
        $stub->goToStep(1);
    }

    public function test goToStep throws exception if step does not exist()
    {
        $stepToTest = 25;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid step: $stepToTest");

        /** @var RouterInterface|MockObject $router */
        $router = $this->createMock(RouterInterface::class);

        $stub = new ActionStub();
        $stub->setRouter($router);
        $stub->goToStep($stepToTest);
    }

    public function test flashMessage should throw exception if no request()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request is not set in step action.');

        $stub = new ActionStub();
        $stub->flashMessage('message');
    }

    public function test flashMessage should throw exception if no session()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No session available in current request.');

        $stub = new ActionStub();
        $stub->setRequest(new Request());
        $stub->flashMessage('message');
    }

    public function test flashMessage with no parameters()
    {
        $stub = new ActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        $stub->flashMessage('message');

        static::assertSame(['message'], $request->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage removes duplicates()
    {
        $stub = new ActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('message');
        $stub->flashMessage('message');

        static::assertSame(['message'], $request->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with parameters()
    {
        $stub = new ActionStub();
        $request = $this->createRequest();
        $stub->setRequest($request);

        // Execute twice to make sure the method keeps only unique messages
        $stub->flashMessage('Message with %replacement%', null, ['%replacement%' => 'REPLACEMENT']);

        static::assertSame(['Message with REPLACEMENT'], $request->getSession()->getFlashBag()->get('error'));
    }

    public function test flashMessage with translator()
    {
        $stub = new ActionStub();
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

    public function test update current step should throw exception if no request()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request is not set in step action.');

        $stub = new ActionStub();
        $stub->updateCharacterStep(null);
    }

    public function test setSteps should throw exception with scalar arguments()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "integer" given.');

        $stub = new ActionStub();
        $stub->setSteps(range(1, 2));
    }

    public function test setSteps should throw exception with wrong object arguments()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface instance, "stdClass" given.');

        $stub = new ActionStub();
        $stub->setSteps([new \stdClass()]);
    }

    public function test getCurrentCharacter should throw exception if no request()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request is not set in step action.');

        $stub = new ActionStub();
        $stub->getCurrentCharacter();
    }

    public function test getCurrentCharacter should throw exception if no session()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No session available in current request.');

        $stub = new ActionStub();
        $stub->setRequest(new Request());
        $stub->getCurrentCharacter();
    }

    public function test getters and setters()
    {
        /** @var EntityManager|MockObject $em */
        $em = $this->createMock(EntityManager::class);
        /** @var TwigEngine|MockObject $templating */
        $templating = $this->createMock(TwigEngine::class);

        $stub = new ActionStub();
        $steps = [new Step(1, 'step_1', get_class($stub), 'step_1', [], [])];

        // Just to make sure setters don't fail
        $stub->setEntityManager($em);
        $stub->setTemplating($templating);

        $stub->setStep($step = new Step(1, 'step1', '', '', [], []));
        static::assertSame($step, $stub->getStep());

        $stub->setSteps($steps);
        $stub->setCharacterClass(CharacterStub::class);

        static::assertSame(CharacterStub::class, $stub->getCharacterClass());
        static::assertSame($steps, $stub->getSteps());
    }

    public function test invalid character class in step action()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step action must be a valid class implementing Pierstoval\Bundle\CharacterManagerBundle\Model\CharacterInterface. "stdClass" given.');

        $stub = new ActionStub();
        $stub->setCharacterClass(\stdClass::class);
    }
}
