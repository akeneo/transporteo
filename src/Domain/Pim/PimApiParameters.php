<?php

namespace Akeneo\PimMigration\Domain\Pim;

/**
 * Parameters to connect to a PIM API.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimApiParameters
{
    /** @var string */
    private $baseUri;

    /** @var string */
    private $clientId;

    /** @var string */
    private $secret;

    /** @var string */
    private $userName;

    /** @var string */
    private $userPwd;

    public function __construct($baseUri, $clientId, $secret, $userName, $userPwd)
    {
        $this->baseUri = $baseUri;
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->userName = $userName;
        $this->userPwd = $userPwd;
    }

    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getUserPwd(): string
    {
        return $this->userPwd;
    }
}
