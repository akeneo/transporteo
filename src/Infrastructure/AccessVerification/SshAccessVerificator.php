<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\AccessVerification;

use Akeneo\PimMigration\Domain\MigrationStep\s030_AccessVerification\AccessException;
use Akeneo\PimMigration\Domain\MigrationStep\s030_AccessVerification\AccessVerificator;
use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\Pim\SshConnection;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

/**
 * Verify through SSH the Enterprise Edition access.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshAccessVerificator implements AccessVerificator
{
    /**
     * {@inheritdoc}
     */
    public function verify(PimConnection $pimConnection): void
    {
        if (!$pimConnection instanceof SshConnection) {
            throw new \InvalidArgumentException('%s expected, %s given', SshConnection::class, get_class($pimConnection));
        }

        $ssh = new SSH2($pimConnection->getHost(), $pimConnection->getPort());
        $rsa = new RSA();

        $rsa->load($pimConnection->getSshKey()->getKey());

        if (!$ssh->login($pimConnection->getUsername(), $rsa)) {
            throw new AccessException('You are not allowed to download the EnterpriseEdition');
        }
    }
}
