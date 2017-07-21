<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
use Akeneo\PimMigration\Infrastructure\SshKey;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

/**
 * Verify through SSH the Enterprise Edition access.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshEnterpriseEditionAccessVerificator implements EnterpriseEditionAccessVerificator
{
    /** @var SshKey */
    private $sshKey;

    public function __construct(SshKey $sshKey)
    {
        $this->sshKey = $sshKey;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(SourcePim $sourcePim): void
    {
        $serverAccess = ServerAccessInformation::fromString($sourcePim->getEnterpriseRepository(), $this->sshKey);

        $ssh = new SSH2($serverAccess->getHost(), $serverAccess->getPort());
        $key = new RSA();

        $key->load($this->sshKey->getKey());

        if (!$ssh->login($serverAccess->getUsername(), $key)) {
            throw new EnterpriseEditionAccessException('You are not allowed to download the EnterpriseEdition');
        }
    }
}
