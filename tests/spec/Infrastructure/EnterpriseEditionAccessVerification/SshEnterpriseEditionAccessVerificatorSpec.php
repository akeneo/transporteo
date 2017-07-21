<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\SshKey;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\EnterpriseEditionAccessVerification\SshEnterpriseEditionAccessVerificator;
use PhpSpec\ObjectBehavior;

/**
 * Spec for SSH enterprise edition access verificator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshEnterpriseEditionAccessVerificatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SshEnterpriseEditionAccessVerificator::class);
    }

    public function it_should(SourcePim $sourcePim)
    {
        $sshKey = new SshKey('/home/docker/.ssh/akeneo');
        $sourcePim->getEnterpriseRepository()->willReturn(  'ssh://git@distribution.akeneo.com:443/pim-enterprise-dev-nanou-migration.git');
        $this->verify($sourcePim, $sshKey);
    }
}
