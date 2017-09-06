<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

/**
 * Build the pim configuration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface PimConfigurator
{
    public function configure(PimServerInformation $pimServerInfo): PimConfiguration;
}
