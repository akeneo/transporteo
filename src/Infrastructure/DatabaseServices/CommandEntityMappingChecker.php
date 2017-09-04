<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DatabaseServices;

use Akeneo\PimMigration\Domain\DataMigration\EntityMappingChecker;
use Akeneo\PimMigration\Domain\DataMigration\EntityMappingException;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Infrastructure\Command\CommandLauncher;
use Akeneo\PimMigration\Infrastructure\Command\UnsuccessfulCommandException;

/**
 * Check if the mapping of an entity is good.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class CommandEntityMappingChecker implements EntityMappingChecker
{
    /** @var CommandLauncher */
    private $commandLauncher;

    public function __construct(CommandLauncher $commandLauncher)
    {
        $this->commandLauncher = $commandLauncher;
    }

    /**
     * @throws EntityMappingException
     * @throws \InvalidArgumentException
     */
    public function check(Pim $pim, string $entityClassPath): void
    {
        try {
            $commandResult = $this->commandLauncher->runCommand(new DoctrineMappingInfoCommand(), $pim->absolutePath(), false);
        } catch (UnsuccessfulCommandException $exception) {
            throw new EntityMappingException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $entityMappingResults = array_filter(explode(PHP_EOL, $commandResult->getOutput()), function ($element) use ($entityClassPath) {
            return false !== strpos($element, $entityClassPath);
        });

        if (empty($entityMappingResults)) {
            throw new \InvalidArgumentException(sprintf('The entity %s is not configured', $entityClassPath));
        }

        $entityMappingResult = array_pop($entityMappingResults);

        if (false === strpos($entityMappingResult, '[OK]')) {
            throw new EntityMappingException(sprintf('The entity %s is not well mapped, please check your mapping', $entityClassPath));
        }
    }
}
