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

if (\function_exists('xdebug_set_filter')) {
    $rootDir = \dirname(__DIR__).DIRECTORY_SEPARATOR;
    \xdebug_set_filter(
        \constant('XDEBUG_FILTER_CODE_COVERAGE'),
        \constant('XDEBUG_PATH_WHITELIST'),
        [
            $rootDir.'Action'.DIRECTORY_SEPARATOR,
            $rootDir.'Controller'.DIRECTORY_SEPARATOR,
            $rootDir.'DependencyInjection'.DIRECTORY_SEPARATOR,
            $rootDir.'Entity'.DIRECTORY_SEPARATOR,
            $rootDir.'Exception'.DIRECTORY_SEPARATOR,
            $rootDir.'Model'.DIRECTORY_SEPARATOR,
            $rootDir.'Registry'.DIRECTORY_SEPARATOR,
            $rootDir.'Resolver'.DIRECTORY_SEPARATOR,
        ]
    );
}

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require $file;

(function(){
    if (\file_exists($dbFile = __DIR__.'/build/database_test.db')) {
        unlink($dbFile);
    }

    $kernel = new TestKernel('test', true);
    $kernel->boot();
    $application = new Application($kernel);
    $application->setAutoExit(false);

    $application->run(new ArrayInput(['doctrine:database:create']));
    $application->run(new ArrayInput(['doctrine:schema:create']));

    $kernel->shutdown();
})();
