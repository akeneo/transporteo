<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\ReferenceDataMigration;

use Akeneo\PimMigration\Domain\Command\CommandLauncher;
use Akeneo\PimMigration\Domain\Command\UnixCommandResult;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\ReferenceDataMigration\DebugConfigCommand;
use Akeneo\PimMigration\Domain\ReferenceDataMigration\ReferenceDataMigrator;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use PhpSpec\ObjectBehavior;

/**
 * Spec for ReferenceDataMigrator.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ReferenceDataMigratorSpec extends ObjectBehavior
{
    public function let(CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($commandLauncher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReferenceDataMigrator::class);
    }

    public function it_migrates(
        UnixCommandResult $commandResult,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        $commandLauncher
    ) {
        $commandResultOutput = <<<TEXT
# Current configuration for "PimReferenceDataBundle"
pim_reference_data:
    -
        class: Acme\Bundle\AppBundle\Entity\Fabric
        type: multi
    -
        class: Acme\Bundle\AppBundle\Entity\Color
        type: simple


TEXT;
        $sourcePim->getPath()->willReturn('a_path');
        $commandResult->getOutput()->willReturn($commandResultOutput);
        $commandLauncher->runCommand(new DebugConfigCommand('PimReferenceDataBundle'), 'a_path', false);

        $result = [
            'pim_reference_data' => [
                [
                    'class' => 'Acme\Bundle\AppBundle\Entity\Fabric',
                    'type'  => 'multi'
                ],
                [
                    'class' => 'Acme\Bundle\AppBundle\Entity\Color',
                    'type'  => 'simple'
                ]
            ]
        ];
    }
}
