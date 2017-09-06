<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ConsoleHelper;
use Akeneo\PimMigration\Domain\DataMigration\BundleConfigFetcher;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use PhpSpec\ObjectBehavior;

/**
 * Spec for BundleConfigFetcher.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class BundleConfigFetcherSpec extends ObjectBehavior
{
    public function let(ConsoleHelper $consoleHelper)
    {
        $this->beConstructedWith($consoleHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(BundleConfigFetcher::class);
    }

    public function it_fetches_the_config(
        SourcePim $sourcePim,
        CommandResult $commandResult,
        $consoleHelper
    ) {
        $sourcePim->absolutePath()->willReturn('/a-path');

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

        $consoleHelper->execute($sourcePim, new SymfonyCommand('debug:config a-bundle-name'))->willReturn($commandResult);

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
