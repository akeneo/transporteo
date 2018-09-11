<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\DataMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;
use Akeneo\PimMigration\Domain\Command\UnsuccessfulCommandException;
use Akeneo\PimMigration\Domain\Pim\Pim;

/**
 * Check if the mapping of an entity is good.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class EntityMappingChecker
{
    /** @var ChainedConsole */
    private $chainedConsole;

    public function __construct(ChainedConsole $chainedConsole)
    {
        $this->chainedConsole = $chainedConsole;
    }

    /**
     * @throws EntityMappingException
     * @throws \InvalidArgumentException
     */
    public function check(Pim $pim, string $entityClassPath): void
    {
        try {
            $this->chainedConsole->execute(
              new SymfonyCommand('cache:clear', SymfonyCommand::PROD),
              $pim
            );

            $commandResult = $this->chainedConsole->execute(
                new SymfonyCommand('doctrine:mapping:info', SymfonyCommand::PROD),
                $pim
            );
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
