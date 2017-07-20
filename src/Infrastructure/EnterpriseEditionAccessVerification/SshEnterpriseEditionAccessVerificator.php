<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessException;
use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use phpseclib\Net\SSH2;

/**
 * Verify through SSH the Enterprise Edition access.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshEnterpriseEditionAccessVerificator implements EnterpriseEditionAccessVerificator
{
    /**
     * {@inheritdoc}
     */
    public function verify(SourcePim $sourcePim): void
    {
        $repository = $sourcePim->getEnterpriseRepository();

        $urlParsed = parse_url($repository);

        $ssh = new SSH2($urlParsed['host'], $urlParsed['port']);

        $val = $ssh->login($urlParsed['user']);
        if (!$ssh->login($urlParsed['user'])) {
            throw new EnterpriseEditionAccessException('BOUBOUBOUB');
        }
        echo 'coucou';
    }
}
