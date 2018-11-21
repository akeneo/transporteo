<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Pim;

use Akeneo\PimMigration\Domain\Pim\PimConnection;
use Akeneo\PimMigration\Infrastructure\SshKey;

/**
 * Representation of a SshConnection.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SshConnection implements PimConnection
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    public function __construct(string $host, int $port, string $username, ?string $password = null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public static function fromString(string $serverInformation)
    {
        $parsedServerInformation = parse_url($serverInformation);

        return new self(
            $parsedServerInformation['host'],
            $parsedServerInformation['port'],
            $parsedServerInformation['user']
        );
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
}
