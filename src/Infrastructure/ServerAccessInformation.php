<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ServerAccessInformation
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $username;

    /** @var SshKey */
    private $sshKey;

    public function __construct(string $host, int $port, string $username, SshKey $sshKey)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->sshKey = $sshKey;
    }

    public static function fromString(string $serverInformation, SshKey $sshKey)
    {
        $parsedServerInformation = parse_url($serverInformation);

        return new self(
            $parsedServerInformation['host'],
            $parsedServerInformation['port'],
            $parsedServerInformation['user'],
            $sshKey
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

    public function getSshKey(): SshKey
    {
        return $this->sshKey;
    }
}
