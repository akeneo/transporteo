<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure;

use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\DebugConfigCommand;
use Akeneo\PimMigration\Infrastructure\Command\UnixCommandResult;
use Akeneo\PimMigration\Infrastructure\CommandBundleConfigFetcher;
use PhpSpec\ObjectBehavior;

/**
 * Spec for BundleConfigFetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandBundleConfigFetcherSpec extends ObjectBehavior
{
    public function let(CommandLauncher $commandLauncher)
    {
        $this->beConstructedWith($commandLauncher);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CommandBundleConfigFetcher::class);
    }

    public function it_fetches_the_config(
        SourcePim $sourcePim,
        UnixCommandResult $commandResult,
        $commandLauncher
    ) {
        $sourcePim->getPath()->willReturn('/a-path');

        $yaml = <<<YAML
# Current configuration for "a-bundle-name"
pim_reference_data:
    -
        class: Acme\Bundle\AppBundle\Entity\Fabric
        type: multi
    -
        class: Acme\Bundle\AppBundle\Entity\Color
        type: simple
YAML;

        $commandResult->getOutput()->willReturn($yaml);

        $commandLauncher->runCommand(new DebugConfigCommand('a-bundle-name'),'/a-path', false)->willReturn($commandResult);

        $this->fetch($sourcePim, 'a-bundle-name')->shouldReturn(
            [
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
            ]
        );
    }
}
