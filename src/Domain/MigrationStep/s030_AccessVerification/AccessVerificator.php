<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s030_AccessVerification;

use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Check if a PIM is an EnterpriseEdition it can connect to distribution server.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface AccessVerificator
{
    /**
     * @param SourcePim $sourcePim the Pim to check the access to
     *
     * @throws AccessException when the access is not successful
     */
    public function verify(SourcePim $sourcePim): void;
}
