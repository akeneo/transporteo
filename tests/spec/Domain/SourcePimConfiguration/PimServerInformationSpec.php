<?php

namespace spec\Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\PimServerInformation;
use PhpSpec\ObjectBehavior;

class PimServerInformationSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            '/home/plop/composer.json',
            'nanou-migration',
            'nanouserver',
            2235,
            'nanou'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(PimServerInformation::class);
    }

    public function it_is_not_local_with_information_filled()
    {
        $this->isLocal()->shouldReturn(false);
    }

    public function it_returns_the_parameters_yml_path()
    {
        $this->getParametersYmlPath()->shouldReturn('/home/plop/app/config/parameters.yml');
    }

    public function it_throws_an_exception_if_composer_json_is_not_well_formatted()
    {
        $this->shouldThrow(
            new \InvalidArgumentException('ComposerJsonPath must end by composer.json')
        )->during(
            '__construct',
            ['/home/plop/plop.json', 'nanou-migration', 'nanouserver', 2235, 'nanou']
        );
    }
}
