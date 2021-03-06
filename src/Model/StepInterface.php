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

namespace Pierstoval\Bundle\CharacterManagerBundle\Model;

interface StepInterface
{
    /**
     * Corresponds to the step order position in the steps list.
     * Should be calculated automatically based on the configuration.
     */
    public function getNumber(): int;

    /**
     * Step name must be unique in the whole step collection.
     */
    public function getName(): string;

    /**
     * The label used for human-readable output, like a title or a translation key.
     */
    public function getLabel(): string;

    /**
     * The service name, or class name, that will be used to execute current step's action.
     * The StepsPass will resolve this in a correct service name anyway.
     */
    public function getAction(): string;

    /**
     * This property is here to distinguish steps between different character managers.
     */
    public function getManagerName(): string;

    /**
     * Must be an array containing a list of existing step names.
     * The associated steps are to be cleared when the current step is updated during character creation.
     */
    public function getOnchangeClear(): array;

    /**
     * Must be an array containing a list of existing step names.
     * The current step should not be processed if none of the specified steps are present during character creation.
     */
    public function getDependencies(): array;
}
