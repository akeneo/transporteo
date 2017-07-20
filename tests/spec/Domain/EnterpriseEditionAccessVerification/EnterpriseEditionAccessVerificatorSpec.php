<?php

namespace spec\Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification;

use Akeneo\PimMigration\Domain\EnterpriseEditionAccessVerification\EnterpriseEditionAccessVerificator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use PhpSpec\ObjectBehavior;

class EnterpriseEditionAccessVerificatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(EnterpriseEditionAccessVerificator::class);
    }

    public function it_should(SourcePim $sourcePim)
    {
        $sourcePim->getEnterpriseRepository()->willReturn('ssh://git@distribution.akeneo.com:443/pim-enterprise-dev-nanou-migration.git');
        $this->verify($sourcePim);
    }
}
