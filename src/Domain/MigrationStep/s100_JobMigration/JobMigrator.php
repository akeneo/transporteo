<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;

/**
 * Job migration `batch_execution`.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class JobMigrator
{
    /** @var array */
    private $jobMigrators = [];

    /** @var ChainedConsole */
    private $console;

    public function __construct(ChainedConsole $console)
    {
        $this->console = $console;
    }

    /**
     * @throws JobMigrationException
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            foreach ($this->jobMigrators as $jobMigrator) {
                $jobMigrator->migrate($sourcePim, $destinationPim);
            }

            $queries = [];

            $queries[] = sprintf(
                'ALTER TABLE %s.akeneo_batch_job_execution ADD COLUMN raw_parameters LONGTEXT NOT NULL AFTER log_file',
                $destinationPim->getDatabaseName()
            );

            $queries = array_merge($queries, $this->getUpdateJobInstanceQueries($destinationPim));

            foreach ($queries as $query) {
                $this->console->execute(new MySqlExecuteCommand($query), $destinationPim);
            }
        } catch (DataMigrationException $exception) {
            throw new JobMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    public function addJobMigrator(DataMigrator $jobMigrator): void
    {
        $this->jobMigrators[] = $jobMigrator;
    }

    private function getUpdateJobInstanceQueries(DestinationPim $destinationPim): array
    {
        $jobInstancesCode = [
            "'add_product_value'",
            "'csv_association_type_export'",
            "'csv_association_type_import'",
            "'csv_attribute_export'",
            "'csv_attribute_group_export'",
            "'csv_attribute_group_import'",
            "'csv_attribute_import'",
            "'csv_attribute_option_export'",
            "'csv_attribute_option_import'",
            "'csv_category_export'",
            "'csv_category_import'",
            "'csv_channel_export'",
            "'csv_channel_import'",
            "'csv_currency_export'",
            "'csv_currency_import'",
            "'csv_family_export'",
            "'csv_family_import'",
            "'csv_family_variant_export'",
            "'csv_family_variant_import'",
            "'csv_group_export'",
            "'csv_group_import'",
            "'csv_group_type_export'",
            "'csv_group_type_import'",
            "'csv_locale_export'",
            "'csv_locale_import'",
            "'csv_product_export'",
            "'csv_product_grid_context_quick_export'",
            "'csv_product_import'",
            "'csv_product_model_export'",
            "'csv_product_model_import'",
            "'csv_product_quick_export'",
            "'edit_common_attributes'",
            "'remove_product_value'",
            "'set_attribute_requirements'",
            "'update_product_value'",
            "'xlsx_association_type_export'",
            "'xlsx_association_type_import'",
            "'xlsx_attribute_export'",
            "'xlsx_attribute_group_export'",
            "'xlsx_attribute_group_import'",
            "'xlsx_attribute_import'",
            "'xlsx_attribute_option_export'",
            "'xlsx_attribute_option_import'",
            "'xlsx_category_export'",
            "'xlsx_category_import'",
            "'xlsx_channel_export'",
            "'xlsx_channel_import'",
            "'xlsx_currency_export'",
            "'xlsx_currency_import'",
            "'xlsx_family_export'",
            "'xlsx_family_import'",
            "'xlsx_family_variant_export'",
            "'xlsx_family_variant_import'",
            "'xlsx_group_export'",
            "'xlsx_group_import'",
            "'xlsx_group_type_export'",
            "'xlsx_group_type_import'",
            "'xlsx_locale_export'",
            "'xlsx_locale_import'",
            "'xlsx_product_export'",
            "'xlsx_product_grid_context_quick_export'",
            "'xlsx_product_import'",
            "'xlsx_product_model_export'",
            "'xlsx_product_model_import'",
            "'xlsx_product_quick_export'",
        ];

        $query = sprintf(
            'SELECT code, raw_parameters FROM %s.akeneo_batch_job_instance WHERE code IN (%s)',
            $destinationPim->getDatabaseName(),
            implode(', ', $jobInstancesCode)
        );

        $jobInstances = $this->console->execute(new MySqlQueryCommand($query), $destinationPim)->getOutput();

        $migratedJobInstances = [];

        foreach ($jobInstances as $jobInstance) {
            $parameters = unserialize($jobInstance['raw_parameters']);
            $parameters['user_to_notify'] = null;
            $parameters['is_user_authenticated'] = false;
            $jobInstance['raw_parameters'] = serialize($parameters);

            $migratedJobInstances[] = $jobInstance;
        }

        return array_map(function ($migratedJobInstance) use ($destinationPim) {
            return sprintf(
                "UPDATE %s.akeneo_batch_job_instance SET raw_parameters = '%s' WHERE code = '%s'",
                $destinationPim->getDatabaseName(),
                $migratedJobInstance['raw_parameters'],
                $migratedJobInstance['code']
            );
        }, $migratedJobInstances);
    }
}
