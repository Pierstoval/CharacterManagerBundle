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

use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Filesystem\Filesystem;

require \dirname(__DIR__).'/vendor/autoload.php';

echo "\n[Test bootstrap] Bootstraping test suite...";

if (\function_exists('xdebug_set_filter')) {
    echo "\n[Test bootstrap] Xdebug enabled, activate coverage whitelist filter...";
    xdebug_set_filter(
        \defined('XDEBUG_FILTER_CODE_COVERAGE') ? \constant('XDEBUG_FILTER_CODE_COVERAGE') : 512,
        \defined('XDEBUG_PATH_INCLUDE') ? \constant('XDEBUG_PATH_INCLUDE') : 1,
        [
            \dirname(__DIR__).'/src/',
        ]
    );
}

echo "\n[Test bootstrap] Clearing test cache...";

(new Filesystem())->remove(dirname(__DIR__).'/build');

(static function (): void {
    echo "\n[Test bootstrap] Creating test database...\n";

    $kernel = new TestKernel('test', true);
    $kernel->boot();
    $application = new Application($kernel);
    $application->setAutoExit(false);

    $application->run(new ArrayInput(['doctrine:database:create']));
    $application->run(new ArrayInput(['doctrine:schema:create']));

    $kernel->shutdown();
})();

echo "\n[Test bootstrap] Done!\n";
