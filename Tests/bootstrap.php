<?php

/**
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <pierstoval@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require $file;

$fs = new Filesystem();
$fs->remove(__DIR__.'/build/');

$kernel = new TestKernel('test', true);
$kernel->boot();
$application = new Application($kernel);
$application->setAutoExit(false);

$application->run(new ArrayInput(['doctrine:database:create']));
$application->run(new ArrayInput(['doctrine:schema:create']));

$kernel->shutdown();

// Unset this to avoid PHPUnit to dump these globals.
unset($application, $file, $fs, $kernel);
