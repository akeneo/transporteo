<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\Pim\AkeneoPimClientInterface;
use Akeneo\Pim\Api\ProductApiInterface;
use Akeneo\PimMigration\Domain\Pim\PimApiClientBuilder;
use Akeneo\PimMigration\Domain\Pim\PimApiParameters;
use Akeneo\PimMigration\Domain\PrinterAndAsker;
use Akeneo\PimMigration\Infrastructure\MigrationStep\S015FromSourcePimConfiguredToSourcePimApiConfigured;
use Akeneo\PimMigration\Infrastructure\TransporteoStateMachine;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Source PIM API configuration specs.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S015FromSourcePimConfiguredToSourcePimApiConfiguredSpec extends ObjectBehavior
{
    public function let(Translator $translator, LoggerInterface $logger, PimApiClientBuilder $apiClientBuilder, PrinterAndAsker $printerAndAsker)
    {
        $this->beConstructedWith($translator, $logger, $apiClientBuilder);
        $this->setPrinterAndAsker($printerAndAsker);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(S015FromSourcePimConfiguredToSourcePimApiConfigured::class);
    }

    public function it_configures_a_source_pim_api(
        Event $event,
        TransporteoStateMachine $stateMachine,
        AkeneoPimClientInterface $apiClient,
        ProductApiInterface $productApi,
        $printerAndAsker,
        $translator,
        $apiClientBuilder
    )
    {
        $event->getSubject()->willReturn($stateMachine);

        $baseUri = 'http://localhost';
        $question = 'What is the base URI to request the API of the source PIM?';
        $translator
            ->trans('from_source_pim_configured_to_source_pim_api_configured.on_source_pim_api_configuration.base_uri.question')
            ->willReturn($question);
        $printerAndAsker->askSimpleQuestion($question, '', Argument::any())->willReturn($baseUri);

        $clientId = 'clientId';
        $question = 'What is the client id to use to authenticate to the API of the source PIM?';
        $translator
            ->trans('from_source_pim_configured_to_source_pim_api_configured.on_source_pim_api_configuration.client_id_question')
            ->willReturn($question);
        $printerAndAsker->askSimpleQuestion($question)->willReturn($clientId);

        $secret = 'secret';
        $question = 'What is the secret associated to this client?';
        $translator
            ->trans('from_source_pim_configured_to_source_pim_api_configured.on_source_pim_api_configuration.secret_question')
            ->willReturn($question);
        $printerAndAsker->askSimpleQuestion($question)->willReturn($secret);

        $userName = 'userName';
        $question = 'What is the username to use to authenticate to the API of the source PIM?';
        $translator
            ->trans('from_source_pim_configured_to_source_pim_api_configured.on_source_pim_api_configuration.user_name_question')
            ->willReturn($question);
        $printerAndAsker->askSimpleQuestion($question)->willReturn($userName);

        $userPwd = 'userPwd';
        $question = 'What is the password associated to this username?';
        $translator
            ->trans('from_source_pim_configured_to_source_pim_api_configured.on_source_pim_api_configuration.user_pwd_question')
            ->willReturn($question);
        $printerAndAsker->askSimpleQuestion($question)->willReturn($userPwd);

        $sourceApiParameters = new PimApiParameters(
            $baseUri,
            $clientId,
            $secret,
            $userName,
            $userPwd
        );

        $apiClientBuilder->build($sourceApiParameters)->willReturn($apiClient);

        $apiClient->getProductApi()->willReturn($productApi);
        $productApi->all(1)->shouldBeCalled();

        $stateMachine->setSourcePimApiParameters($sourceApiParameters)->shouldBeCalled();

        $this->onSourcePimApiConfiguration($event);
    }
}
