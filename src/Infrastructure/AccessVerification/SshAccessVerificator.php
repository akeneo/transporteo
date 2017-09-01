<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\AccessVerification;

use Akeneo\PimMigration\Domain\MigrationStep\s30_AccessVerification\AccessException;
use Akeneo\PimMigration\Domain\MigrationStep\s30_AccessVerification\AccessVerificator;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\ServerAccessInformation;
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
    /** @var SSH2 */
    private $ssh;

    /** @var RSA */
    private $rsa;

    /** @var ServerAccessInformation */
    private $serverAccessInformation;

    public function __construct(SSH2 $ssh, RSA $rsa, ServerAccessInformation $serverAccessInformation)
    {
        $this->ssh = $ssh;
        $this->rsa = $rsa;
        $this->serverAccessInformation = $serverAccessInformation;
    }

    /**
     * {@inheritdoc}
     */
    public function verify(SourcePim $sourcePim): void
    {
        if (!$this->ssh->login($this->serverAccessInformation->getUsername(), $this->rsa)) {
            throw new AccessException('You are not allowed to download the EnterpriseEdition');
        }
    }
}
