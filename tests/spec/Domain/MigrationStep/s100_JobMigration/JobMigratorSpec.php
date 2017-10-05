<?php

declare(strict_types=1);

namespace spec\Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration;;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\CommandResult;
use Akeneo\PimMigration\Domain\Command\MysqlEscaper;
use Akeneo\PimMigration\Domain\Command\MySqlExecuteCommand;
use Akeneo\PimMigration\Domain\Command\MySqlQueryCommand;
use Akeneo\PimMigration\Domain\DataMigration\DatabaseQueryExecutor;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrationException;
use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrationException;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Pim\SourcePim;
use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrator;
use PhpSpec\ObjectBehavior;

/**
 * Job Migrator Spec.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class JobMigratorSpec extends ObjectBehavior
{
    public function let(ChainedConsole $console, MysqlEscaper $mysqlEscaper)
    {
        $this->beConstructedWith($console, $mysqlEscaper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(JobMigrator::class);
    }

    public function it_successfully_migrates_jobs(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim,
        CommandResult $commandResult,
        $console,
        $mysqlEscaper
    ) {
        $this->addJobMigrator($migratorOne);
        $this->addJobMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->shouldBeCalled();

        $console->execute(
            new MySqlExecuteCommand('ALTER TABLE akeneo_batch_job_execution ADD COLUMN raw_parameters LONGTEXT NOT NULL AFTER log_file, ADD COLUMN health_check_time DATETIME NULL AFTER updated_time'),
            $destinationPim
        )->shouldBeCalled();

        $console->execute(
            new MySqlExecuteCommand("INSERT INTO akeneo_batch_job_instance (code,label,job_name,status,connector,raw_parameters,type) VALUES ('compute_product_models_descendants','Compute product models descendants','compute_product_models_descendants',0,'internal','a:0:{}','compute_product_models_descendants')"),
            $destinationPim
        )->shouldBeCalled();

        $rawParameters = 'a:7:{s:8:"filePath";s:25:"/tmp/association_type.csv";s:9:"delimiter";s:1:";";s:9:"enclosure";s:1:""";s:6:"escape";s:1:"\";s:10:"withHeader";b:1;s:13:"uploadAllowed";b:1;s:25:"invalid_items_file_format";s:3:"csv";}';

        $commandResult->getOutput()->willReturn([['code' => 'add_product_value', 'raw_parameters' => $rawParameters]]);

        $console->execute(
            new MySqlQueryCommand($this->getSelectJobInstanceQuery($destinationPim)),
            $destinationPim
        )->willReturn($commandResult);

        $parameters = unserialize($rawParameters);
        $parameters['user_to_notify'] = null;
        $parameters['is_user_authenticated'] = false;
        $parameters = serialize($parameters);

        $mysqlEscaper->escape($parameters, $destinationPim)->willReturn("'".$parameters."'");

        $query = sprintf("UPDATE akeneo_batch_job_instance SET raw_parameters = '%s' WHERE code = 'add_product_value'", $parameters);

        $console->execute(new MySqlExecuteCommand($query), $destinationPim)->shouldBeCalled();

        $this->migrate($sourcePim, $destinationPim);
    }

    public function it_throws_an_exception(
        DataMigrator $migratorOne,
        DataMigrator $migratorTwo,
        SourcePim $sourcePim,
        DestinationPim $destinationPim
    ) {
        $this->addJobMigrator($migratorOne);
        $this->addJobMigrator($migratorTwo);

        $migratorOne->migrate($sourcePim, $destinationPim)->shouldBeCalled();
        $migratorTwo->migrate($sourcePim, $destinationPim)->willThrow(new DataMigrationException());

        $this->shouldThrow(new JobMigrationException())->during('migrate', [$sourcePim, $destinationPim]);
    }

    private function getSelectJobInstanceQuery(Pim $pim): string
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
            "'add_tags_to_assets'",
            "'apply_assets_mass_upload'",
            "'approve_product_draft'",
            "'classify_assets'",
            "'csv_asset_category_export'",
            "'csv_asset_category_import'",
            "'csv_asset_export'",
            "'csv_asset_import'",
            "'csv_asset_variation_export'",
            "'csv_option_export'",
            "'csv_option_import'",
            "'csv_product_import_with_rules'",
            "'csv_product_proposal_import'",
            "'csv_published_product_export'",
            "'csv_published_product_grid_context_quick_export'",
            "'csv_published_product_quick_export'",
            "'project_calculation'",
            "'publish_product'",
            "'refresh_project_completeness_calculation'",
            "'refuse_product_draft'",
            "'rule_impacted_product_count'",
            "'unpublish_product'",
            "'xlsx_asset_category_export'",
            "'xlsx_asset_category_import'",
            "'xlsx_asset_export'",
            "'xlsx_asset_import'",
            "'xlsx_asset_variation_export'",
            "'xlsx_option_export'",
            "'xlsx_option_import'",
            "'xlsx_product_import_with_rules'",
            "'xlsx_product_proposal_import'",
            "'xlsx_published_product_export'",
            "'xlsx_published_product_grid_context_quick_export'",
            "'xlsx_published_product_quick_export'",
            "'yml_asset_channel_configuration_export'",
            "'yml_asset_channel_configuration_import'",
            "'yml_rule_export'",
            "'yml_rule_import'",
        ];

        $query = sprintf(
            'SELECT code, raw_parameters FROM akeneo_batch_job_instance WHERE code IN (%s)',
            implode(', ', $jobInstancesCode)
        );

        return $query;
    }
}
