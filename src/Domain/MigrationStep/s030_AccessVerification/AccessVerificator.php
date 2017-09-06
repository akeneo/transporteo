<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s030_AccessVerification;

use Akeneo\PimMigration\Domain\Pim\PimConnection;

/**
 * Check if a PIM is an EnterpriseEdition it can connect to distribution server.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface AccessVerificator
{
    /**
     * @throws AccessException when the access is not successful
     */
    public function verify(PimConnection $connection): void;
}
