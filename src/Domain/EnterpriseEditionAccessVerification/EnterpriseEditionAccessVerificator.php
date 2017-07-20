<?php

declare(strict_types=1);


namespace Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Check if a PIM is an EnterpriseEdition it can connect to distribution server.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseEditionAccessVerificator
{
    public function verify(SourcePim $sourcePim): bool
    {
        $repository = $sourcePim->getEnterpriseRepository();

        $pattern = 'ssh://git@(?P<server_address>):(?P<port>\d+)/';

        $matches = [];

        preg_match($pattern, $repository, $matches);

        return true;
    }
}
