<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\MigrationStep\s120_ExtraDataMigration;

use Akeneo\PimMigration\Domain\DataMigration\DataMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableMigrator;
use Akeneo\PimMigration\Domain\DataMigration\TableNamesFetcher;
use Akeneo\PimMigration\Domain\MigrationStep\s50_DestinationPimInstallation\DestinationPim;
use Akeneo\PimMigration\Domain\MigrationStep\s20_SourcePimDetection\SourcePim;

/**
 * Migrator for extra data.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ExtraDataMigrator implements DataMigrator
{
    /** @var TableMigrator */
    private $tableMigrator;

    /** @var TableNamesFetcher */
    private $tableNamesFetcher;

    public function __construct(TableMigrator $tableMigrator, TableNamesFetcher $tableNamesFetcher)
    {
        $this->tableMigrator = $tableMigrator;
        $this->tableNamesFetcher = $tableNamesFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function migrate(SourcePim $sourcePim, DestinationPim $destinationPim): void
    {
        try {
            $tablesInSourcePim = $this->tableNamesFetcher->getTableNames($sourcePim);

            $extraTables = array_diff($tablesInSourcePim, $this->getSourcePimStandardTables());

            foreach ($extraTables as $extraTable) {
                $this->tableMigrator->migrate($sourcePim, $destinationPim, $extraTable);
            }
        } catch (\Exception $exception) {
            throw new ExtraDataMigrationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    protected function getSourcePimStandardTables(): array
    {
        //TODO add EE tables
        return [
            'acl_classes',
            'acl_entries',
            'acl_object_identities',
            'acl_object_identity_ancestors',
            'acl_security_identities',
            'akeneo_batch_job_execution',
            'akeneo_batch_job_instance',
            'akeneo_batch_step_execution',
            'akeneo_batch_warning',
            'akeneo_file_storage_file_info',
            'oro_access_group',
            'oro_access_role',
            'oro_config',
            'oro_config_value',
            'oro_navigation_history',
            'oro_navigation_item',
            'oro_navigation_item_pinbar',
            'oro_navigation_pagestate',
            'oro_navigation_title',
            'oro_user',
            'oro_user_access_group',
            'oro_user_access_group_role',
            'oro_user_access_role',
            'pim_api_access_token',
            'pim_api_auth_code',
            'pim_api_client',
            'pim_api_refresh_token',
            'pim_catalog_association',
            'pim_catalog_association_group',
            'pim_catalog_association_product',
            'pim_catalog_association_type',
            'pim_catalog_association_type_translation',
            'pim_catalog_attribute',
            'pim_catalog_attribute_group',
            'pim_catalog_attribute_group_translation',
            'pim_catalog_attribute_locale',
            'pim_catalog_attribute_option',
            'pim_catalog_attribute_option_value',
            'pim_catalog_attribute_requirement',
            'pim_catalog_attribute_translation',
            'pim_catalog_category',
            'pim_catalog_category_product',
            'pim_catalog_category_translation',
            'pim_catalog_channel',
            'pim_catalog_channel_currency',
            'pim_catalog_channel_locale',
            'pim_catalog_channel_translation',
            'pim_catalog_completeness',
            'pim_catalog_currency',
            'pim_catalog_family',
            'pim_catalog_family_attribute',
            'pim_catalog_family_translation',
            'pim_catalog_group',
            'pim_catalog_group_attribute',
            'pim_catalog_group_product',
            'pim_catalog_group_translation',
            'pim_catalog_group_type',
            'pim_catalog_group_type_translation',
            'pim_catalog_locale',
            'pim_catalog_metric',
            'pim_catalog_product',
            'pim_catalog_product_template',
            'pim_catalog_product_value',
            'pim_catalog_product_value_option',
            'pim_catalog_product_value_price',
            'pim_comment_comment',
            'pim_datagrid_view',
            'pim_enrich_sequential_edit',
            'pim_notification_notification',
            'pim_notification_user_notification',
            'pim_session',
            'pim_user_default_datagrid_view',
            'pim_versioning_version',
        ];
    }
}
