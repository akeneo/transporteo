<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\MigrationStep;

use Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration\EnterpriseEditionDataMigrator;
use Akeneo\PimMigration\Infrastructure\MigrationToolStateMachine;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Event\Event;

/**
 * Migrate enterprise edition data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class S125FromDestinationPimExtraDataMigratedToDestinationPimEnterpriseEditionDataMigrated extends AbstractStateMachineSubscriber implements StateMachineSubscriber
{
    /** @var EnterpriseEditionDataMigrator */
    private $enterpriseEditionDataMigrator;

    public function __construct(
        Translator $translator,
        LoggerInterface $logger,
        EnterpriseEditionDataMigrator $enterpriseEditionDataMigrator
    ) {
        parent::__construct($translator, $logger);
        $this->enterpriseEditionDataMigrator = $enterpriseEditionDataMigrator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.migration_tool.transition.destination_pim_enterprise_edition_data_migration' => 'onDestinationPimEnterpriseEditionDataMigration',
        ];
    }

    public function onDestinationPimEnterpriseEditionDataMigration(Event $event)
    {
        /** @var MigrationToolStateMachine $stateMachine */
        $stateMachine = $event->getSubject();

        if (!$stateMachine->getDestinationPim()->isEnterpriseEdition()) {
            return;
        }

        $this->logEntering(__FUNCTION__);

        $this->printerAndAsker->printMessage($this->translator->trans('from_destination_pim_extra_data_migrated_to_destination_pim_enterprise_edition_data_migrated.message'));

        $this->enterpriseEditionDataMigrator->migrate($stateMachine->getSourcePim(), $stateMachine->getDestinationPim());

        $this->logExit(__FUNCTION__);
    }
}
