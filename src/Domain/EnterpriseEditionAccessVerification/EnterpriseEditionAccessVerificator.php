<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\SshKey;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;

/**
 * Check if a PIM is an EnterpriseEdition it can connect to distribution server.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface EnterpriseEditionAccessVerificator
{
    /**
     * @param SourcePim $sourcePim the Pim to check the access
     * @param SshKey    $sshKey    the Ssh key allowed to download enterprise edition
     *
     * @throws EnterpriseEditionAccessException when the access is not successful
     */
    public function verify(SourcePim $sourcePim, SshKey $sshKey): void;
}
