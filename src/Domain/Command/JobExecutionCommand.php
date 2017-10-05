<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Command;

/**
 * Command to execute a batch job from a PIM.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class JobExecutionCommand extends SymfonyCommand
{
    public function __construct(string $jobCode, array $config)
    {
        // TODO: what email to use ?
        parent::__construct(sprintf(
            "akeneo:batch:job --env=prod --email='admin@example.com' %s --config='%s'", $jobCode, json_encode($config)
        ));
    }
}
