<?php

/*
 * This file is part of the PierstovalCharacterManagerBundle package.
 *
 * (c) Alexandre Rock Ancelet <alex.ancelet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Filesystem\Filesystem;
use Pierstoval\Bundle\CharacterManagerBundle\Tests\Fixtures\App\AppKernel;

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
$autoload = require $file;

$fs = new Filesystem();

// Remove build dir files
if (is_dir(__DIR__.'/../build')) {
    echo "Removing files in the build directory.\n";
    try {
        $fs->remove(__DIR__.'/../build');
    } catch (Exception $e) {
        fwrite(STDERR, $e->getMessage());
        system('rm -rf '.__DIR__.'/../build');
    }
}

AnnotationRegistry::registerLoader(function($class) use ($autoload) {
    $autoload->loadClass($class);

    return class_exists($class, false);
});

include __DIR__.'/Fixtures/App/AppKernel.php';

$kernel = new AppKernel('test', true);
$kernel->boot();

$databaseFile = $kernel->getContainer()->getParameter('database_path');
$application  = new Application($kernel);

if ($fs->exists($databaseFile)) {
    $fs->remove($databaseFile);
}

// Create database
$command = new CreateDatabaseDoctrineCommand();
$application->add($command);
$command->run(new ArrayInput(['command' => 'doctrine:database:create']), new ConsoleOutput());

// Create database schema
$command = new CreateSchemaDoctrineCommand();
$application->add($command);
$command->run(new ArrayInput(['command' => 'doctrine:schema:create']), new ConsoleOutput());

$kernel->shutdown();

unset($kernel, $application, $command, $fs);
