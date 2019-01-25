<?php
/**
 * Created by IntelliJ IDEA.
 * User: Pierstoval
 * Date: 25/01/2019
 * Time: 10:31
 */

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

    /**
     * @dataProvider provide manager names
     */
    public function test getAction with no matching manager throws exception(?string $managerName)
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
        $registry->addStepAction($managerName, $action);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Manager \"$managerName\" does not exist.");

        $registry->getAction('whatever', $managerName);
    }

    /**
     * @dataProvider provide manager names
     */
    public function test getAction with no matching step throws exception(?string $managerName)
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
        $registry->addStepAction($managerName, $action);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Manager \"$managerName\" does not exist.");

        $registry->getAction('whatever', $managerName);
    }

    public function provide manager names()
    {
        yield [null];
        yield ['default'];
    }
}
