<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Common;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\DependencyInjection\Container;

class Application extends SymfonyApplication
{
    /** @var Container */
    private $container;

    public function __construct($name = 'Akeneo PIM Migration Tool', $version = '1.0.0', Container $container)
    {
        $this->container = $container;

        parent::__construct($name, $version);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }
}
