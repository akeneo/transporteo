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

    /** @var string */
    private $port;

    /** @var string */
    private $username;

    /** @var SshKey */
    private $sshKey;

    public function __construct(string $host, string $port, string $username, SshKey $sshKey)
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

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return SshKey
     */
    public function getSshKey(): SshKey
    {
        return $this->sshKey;
    }
}
