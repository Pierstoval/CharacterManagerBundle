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
            ->method('getStep')
            ->willReturn($step);

        $registry = new ActionsRegistry();
        $registry->addStepAction('default', $action);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Manager "inexistent_manager" does not exist.');

        $registry->getAction('whatever_step', 'inexistent_manager');
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
            ->method('getStep')
            ->willReturn($step);

        $registry = new ActionsRegistry();
        $registry->addStepAction('default', $action);

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
