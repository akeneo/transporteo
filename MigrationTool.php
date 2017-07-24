<?php

require __DIR__.'/vendor/autoload.php';

use Akeneo\PimMigration\Infrastructure\Common\ApplicationFactory;
use Akeneo\PimMigration\Infrastructure\UserInterface\Cli\MigrationTool;

$application = ApplicationFactory::create();

$command = new MigrationTool();

$application->add($command);

$application->run();
