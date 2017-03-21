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
use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Action\StepAction;
use Pierstoval\Bundle\CharacterManagerBundle\Resolver\StepActionResolver;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Action\Stubs\CharacterStub;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractActionTestCase extends TestCase
{
    /**
     * @return Request
     */
    public function createRequest()
    {
        $request = new Request();

        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    /**
     * @param string $actionClass
     * @param array|null $resolverOptions
     * @param array $mockOptions
     *
     * @return StepAction
     */
    public function createStepAction($actionClass, array $resolverOptions = null, array $mockOptions = [])
    {
        if (!is_a($actionClass, StepAction::class, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Class must extend %s, %s given',
                StepAction::class, $actionClass
            ));
        }

        $resolver = $this->createResolver($actionClass, $resolverOptions);

        /** @var StepAction $instance */
        $instance = new $actionClass;

        $instance->setCharacterClass(CharacterStub::class);

        $steps = $resolver->getSteps();
        reset($steps);

        $instance->setSteps($steps);
        $instance->setStep(current($steps));

        $this->mockServicesForStepAction($instance, $mockOptions);

        return $instance;
    }

    /**
     * @param string $actionClass
     * @param array|null $options
     *
     * @return StepActionResolver
     */
    public function createResolver($actionClass, array $options = null)
    {
        if (null === $options) {
            $options = [
                'step_1' => [
                    'action' => $actionClass,
                    'name' =>  'step_1',
                    'label' =>  'Step 1',
                    'depends_on' =>  [],
                    'onchange_clear' =>  [],
                    'step' =>  1,
                ],
                'step_2' => [
                    'action' => $actionClass,
                    'name' =>  'step_2',
                    'label' =>  'Step 2',
                    'depends_on' =>  [],
                    'onchange_clear' =>  [],
                    'step' =>  2,
                ],
                'final_step' => [
                    'action' => $actionClass,
                    'name' =>  'final_step',
                    'label' =>  'Final step',
                    'depends_on' =>  [],
                    'onchange_clear' =>  [],
                    'step' =>  3,
                ],
            ];
        }

        return new StepActionResolver($options);
    }

    /**
     * @param StepAction $instance
     * @param array $mockOptions
     */
    private function mockServicesForStepAction(StepAction $instance, array $mockOptions = [])
    {
        $mockOptions = array_merge([
            'redirect_path' => '/generated_step',
        ], $mockOptions);

        /** @var RouterInterface|\PHPUnit_Framework_MockObject_MockObject $router */
        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')
            ->withAnyParameters()
            ->willReturn($mockOptions['redirect_path'])
        ;

        /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $entityManager */
        $entityManager = $this->createMock(EntityManager::class);

        /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject $translator */
        $translator = $this->createMock(TranslatorInterface::class);

        /** @var TwigEngine|\PHPUnit_Framework_MockObject_MockObject $templating */
        $templating = $this->createMock(TwigEngine::class);

        $instance->setDefaultServices($entityManager, $templating, $router, $translator);
    }
}
