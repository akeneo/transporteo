<?php

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Infrastructure\ImpossibleConnectionException;
use Akeneo\PimMigration\Infrastructure\UnprocessableCommandException;

/**
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class Ssh
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    public function __construct(string $host, int $port = 22)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function exec(string $command, string $username, ?string $password = null): ?string
    {
        $connection = $this->getAuthenticatedConnection($username, $password);

        $stream = ssh2_exec($connection, $command);
        if (!is_resource($stream)) {
            throw new UnprocessableCommandException(
                sprintf(
                    'Unable to exec the command "%s", on the following host %s@%s:%s',
                    $command,
                    $username,
                    $this->host,
                    $this->port
                )
            );
        }

        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);

        $this->disconnect($connection);

        return $output;
    }

    public function getAuthenticatedConnection(string $username, ?$password = null)
    {
        $connection = ssh2_connect($this->host, $this->port);

        if (!is_resource($connection)) {
            throw new ImpossibleConnectionException(
                sprintf(
                    'Impossible to connect to %s@%d',
                    $this->host,
                    $this->port
                )
            );
        }

        if (!empty($password)) {
            $res = ssh2_auth_password($connection, $username, $password);
        } else {
            $res = ssh2_auth_agent($connection, $username);
        }

        if (false === $res) {
            throw new ImpossibleConnectionException(
                sprintf(
                    'Impossible to login to %s@%s:%d using ssh local agent, try to run ssh-add before retry',
                    $username,
                    $this->host,
                    $this->port
                )
            );
        }

        return $connection;
    }

    public function disconnect($connection): void
    {
        ssh2_exec($connection, 'logout');
    }
}
