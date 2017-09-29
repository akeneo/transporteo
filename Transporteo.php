<?php

require __DIR__.'/vendor/autoload.php';

use Akeneo\PimMigration\Infrastructure\Common\ApplicationFactory;
use Akeneo\PimMigration\Infrastructure\UserInterface\Cli\Transporteo;
use Akeneo\PimMigration\Infrastructure\UserInterface\Cli\StateMachineDumper;

$application = ApplicationFactory::create();

$migrationCommand = new Transporteo($application->getContainer());
$dumpCommand = new StateMachineDumper($application->getContainer());

$application->add($migrationCommand);
$application->add($dumpCommand);

$application->run();
