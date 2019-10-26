<?php

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Pierstoval\Bundle\CharacterManagerBundle\Action\StepActionInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Model\StepInterface;
use Pierstoval\Bundle\CharacterManagerBundle\Registry\ActionsRegistry;

class ActionsRegistryTest extends TestCase
{
    public function test getAction with no actions throws exception()
    {
        $registry = new ActionsRegistry();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No actions in the registry.');

        $registry->getAction('whatever');
    }

    public function test getAction with no matching manager throws exception()
    {
        $step = $this->createMock(StepInterface::class);
        $step->expects($this->once())
            ->method('getName')
            ->willReturn('default_step_name');

        $action = $this->createMock(StepActionInterface::class);
        $action->expects($this->once())
            ->method('stepName')
            ->willReturn($step->getName());

        $registry = new ActionsRegistry();
        $registry->addStepAction('default', $action->stepName(), $action);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Manager "inexistent_manager" does not exist.');

        $registry->getAction('whatever_step', 'inexistent_manager');
    }

    public function test injecting closure action loads it lazily()
    {
        $step = $this->createMock(StepInterface::class);
        $step->expects($this->once())
            ->method('getName')
            ->willReturn('default_step');

        $action = $this->createMock(StepActionInterface::class);
        $action->expects($this->once())
            ->method('stepName')
            ->willReturn($step->getName());

        $closure = static function () use ($action) {
            return $action;
        };

        $registry = new ActionsRegistry();
        $registry->addStepAction('default', $action->stepName(), $closure);

        static::assertSame($action, $registry->getAction('default_step'));
    }

    public function test injecting closure that returns a wrong object throws exception()
    {
        $step = $this->createMock(StepInterface::class);
        $step->expects($this->once())
            ->method('getName')
            ->willReturn('default_step');

        $action = $this->createMock(StepActionInterface::class);
        $action->expects($this->once())
            ->method('stepName')
            ->willReturn($step->getName());

        $closure = static function () use ($action) {
            return 'wrong';
        };

        $registry = new ActionsRegistry();
        $registry->addStepAction('default', $action->stepName(), $closure);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            "Lazy-loaded action \"%s\" for character manager \"%s\" must be resolved to an instance of \"%s\".\n\"%s\" given.",
            'default_step', 'default', StepActionInterface::class, 'string'
        ));

        static::assertSame($action, $registry->getAction('default_step'));
    }

    /**
     * @dataProvider provide manager names
     */
    public function test getAction with no matching step throws exception(?string $managerName)
    {
        $step = $this->createMock(StepInterface::class);
        $step->expects($this->once())
            ->method('getName')
            ->willReturn('default_step');

        $action = $this->createMock(StepActionInterface::class);
        $action->expects($this->once())
            ->method('stepName')
            ->willReturn($step->getName());

        $registry = new ActionsRegistry();
        $registry->addStepAction('default', $action->stepName(), $action);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Step "inexistent_step" not found in manager "default".');

        $registry->getAction('inexistent_step', $managerName);
    }

    public function provide manager names()
    {
        yield [null];
        yield ['default'];
    }
}
