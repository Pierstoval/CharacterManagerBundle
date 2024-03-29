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

namespace Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Pierstoval\Bundle\CharacterManagerBundle\PierstovalCharacterManagerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new TwigBundle(),
            new PierstovalCharacterManagerBundle(),
        ];
    }

    /**
     * Loads the container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config_'.$this->environment.'.yaml');
    }

    public function getRootDir(): string
    {
        return \dirname(__DIR__, 3);
    }

    public function getProjectDir(): string
    {
        return $this->getRootDir();
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir().'/build/log/'.$this->environment;
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/build/cache/'.$this->environment;
    }
}
