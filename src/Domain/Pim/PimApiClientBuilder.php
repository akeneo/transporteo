<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\Pim\AkeneoPimClientBuilder;

/**
 * Builds a PIM API client.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimApiClientBuilder
{
    public function buildAuthenticatedByPassword(string $baseUri, string $clientId, string $secret, string $userName, string $userPwd): AkeneoPimClientInterface
    {
        $clientBuilder = new AkeneoPimClientBuilder($baseUri);

        return $clientBuilder->buildAuthenticatedByPassword($clientId, $secret, $userName, $userPwd);
    }
}
