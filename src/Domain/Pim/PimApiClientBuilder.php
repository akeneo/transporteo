<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\Pim;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;

/**
 * Builds a PIM API client.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class PimApiClientBuilder
{
    public function build(PimApiParameters $apiParameters): AkeneoPimClientInterface
    {
        $clientBuilder = new AkeneoPimClientBuilder($apiParameters->getBaseUri());

        return $clientBuilder->buildAuthenticatedByPassword(
            $apiParameters->getClientId(),
            $apiParameters->getSecret(),
            $apiParameters->getUserName(),
            $apiParameters->getUserPwd()
        );
    }
}
