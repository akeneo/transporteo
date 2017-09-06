<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

/**
 * Representation of a Pim.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
interface Pim
{
    public function getMysqlHost(): string;

    public function getMysqlPort(): int;

    public function getDatabaseName(): string;

    public function getDatabaseUser(): string;

    public function getDatabasePassword(): string;

    public function isEnterpriseEdition(): bool;

    public function getEnterpriseRepository(): ?string;

    public function absolutePath(): string;

    public function getConnection(): PimConnection;
}
