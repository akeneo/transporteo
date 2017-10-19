<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Common;

use Akeneo\PimMigration\Domain\Command\ChainedConsole;
use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\FileFetcher;
use Akeneo\PimMigration\Domain\FileFetcherRegistry;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloader;
use Akeneo\PimMigration\Domain\MigrationStep\s040_DestinationPimDownload\DestinationPimDownloaderHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstaller;
use Akeneo\PimMigration\Domain\MigrationStep\s050_DestinationPimInstallation\DestinationPimSystemRequirementsInstallerHelper;
use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\StructureMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration\SystemMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration\GroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s125_EnterpriseEditionDataMigration\EnterpriseEditionDataMigrator;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\Loader\YamlFileLoader as TranslationYamlFileLoader;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Workflow\Definition as WorkflowDefinition;
use Symfony\Component\Workflow\EventListener\AuditTrailListener;
use Symfony\Component\Workflow\SupportStrategy\ClassInstanceSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Yaml\Yaml;

/**
 * Symfony container configuration.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
final class ContainerBuilder
{
    private const VAR_DIR = __DIR__ . '/../../../var';

    public static function getContainer(): Container
    {
        $container = new SymfonyContainerBuilder();

        $container->addCompilerPass(new RegisterListenersPass());

        $worklowsDefinition = Yaml::parse(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'transporteo_state_machine.yml'));

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/config'));
        $loader->load('symfony_framework.xml');

        self::loadWorkflowConfiguration($container, $worklowsDefinition['workflows']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/config'));
        $loader->load('services.yml');
        $loader->load('parameters.yml');

        self::loadTranslatorConfiguration($container);
        self::loadLoggerConfiguration($container);

        $container->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('kernel.event_subscriber');

        $container->registerForAutoconfiguration(Console::class)->addTag('transporteo.console');
        $container->registerForAutoconfiguration(FileFetcher::class)->addTag('transporteo.file_fetcher');
        $container->registerForAutoconfiguration(DestinationPimDownloader::class)->addTag('transporteo.destination_pim_downloader');
        $container->registerForAutoconfiguration(DestinationPimSystemRequirementsInstaller::class)->addTag('transporteo.destination_pim_system_requirements_installer');

        $container->compile();

        self::loadRegistry($container, StructureMigrator::class, 'transporteo.structure_migrator', 'addStructureMigrator');
        self::loadRegistry($container, SystemMigrator::class, 'transporteo.system_migrator', 'addSystemMigrator');
        self::loadRegistry($container, JobMigrator::class, 'transporteo.job_migrator', 'addJobMigrator');
        self::loadRegistry($container, GroupMigrator::class, 'transporteo.group_migrator', 'addGroupMigrator');
        self::loadRegistry($container, EnterpriseEditionDataMigrator::class, 'transporteo.enterprise_edition_data_migrator', 'addEnterpriseEditionDataMigrator');

        self::loadRegistry($container, ChainedConsole::class, 'transporteo.console', 'addConsole');
        self::loadRegistry($container, FileFetcherRegistry::class, 'transporteo.file_fetcher', 'addFileFetcher');
        self::loadRegistry($container, DestinationPimDownloaderHelper::class, 'transporteo.destination_pim_downloader', 'addDestinationPimDownloader');
        self::loadRegistry($container, DestinationPimSystemRequirementsInstallerHelper::class, 'transporteo.destination_pim_system_requirements_installer', 'addDestinationPimSystemRequirementsInstaller');

        return $container;
    }

    private static function loadRegistry(
        SymfonyContainerBuilder $containerBuilder,
        string $registyClass,
        string $tag,
        string $addMethod
    ) {
        $definition = $containerBuilder->findDefinition($registyClass);

        $tablesStructureMigrators = $containerBuilder->findTaggedServiceIds($tag);

        foreach ($tablesStructureMigrators as $id => $tags) {
            $definition->addMethodCall($addMethod, array(new Reference($id)));
        }
    }

    private static function loadTranslatorConfiguration(SymfonyContainerBuilder $containerBuilder)
    {
        $translatorDefinition = new Definition(Translator::class, ['en', new MessageSelector()]);
        $translatorDefinition->addMethodCall('addLoader', ['yaml', new TranslationYamlFileLoader()]);
        $translatorDefinition->addMethodCall('addResource', ['yaml', __DIR__.DIRECTORY_SEPARATOR.'messages.en.yml', 'en']);
        $translatorDefinition->addMethodCall('setFallbackLocales', [['en']]);

        $containerBuilder->setDefinition('translator', $translatorDefinition);
    }

    private static function loadLoggerConfiguration(SymfonyContainerBuilder $container)
    {
        $logsDir = self::VAR_DIR . DIRECTORY_SEPARATOR . 'logs';
        if (!is_dir($logsDir)) {
            mkdir($logsDir);
        }

        $loggerDefinition = new Definition(Logger::class, ['app']);
        $loggerDefinition->addMethodCall('pushHandler', [new StreamHandler($logsDir.DIRECTORY_SEPARATOR.'migration.log')]);
        $loggerDefinition->addMethodCall('pushHandler', [new StreamHandler($logsDir.DIRECTORY_SEPARATOR.'error.log', Logger::WARNING)]);

        $container->setDefinition('logger', $loggerDefinition);
        $container->setAlias(LoggerInterface::class, 'logger');
    }

    private static function loadWorkflowConfiguration(SymfonyContainerBuilder $container, $workflows)
    {
        $registryDefinition = $container->getDefinition('workflow.registry');

        foreach ($workflows as $name => $workflow) {
            $type = $workflow['type'];

            $transitions = array();
            foreach ($workflow['transitions'] as $transitionName => $transition) {
                if ($type === 'workflow') {
                    $transitions[] = new Definition(Transition::class, array($transitionName, $transition['from'], $transition['to']));
                } elseif ($type === 'state_machine') {
                    if (!is_array($transition['from'])) {
                        $transition['from'] = [$transition['from']];
                    }
                    foreach ($transition['from'] as $from) {
                        if (!is_array($transition['to'])) {
                            $transition['to'] = [$transition['to']];
                        }
                        foreach ($transition['to'] as $to) {
                            $transitions[] = new Definition(Transition::class, array($transitionName, $from, $to));
                        }
                    }
                }
            }

            // Create a Definition
            $definitionDefinition = new Definition(WorkflowDefinition::class);
            $definitionDefinition->setPublic(false);
            $definitionDefinition->addArgument($workflow['places']);
            $definitionDefinition->addArgument($transitions);
            $definitionDefinition->addTag('workflow.definition', array(
                'name' => $name,
                'type' => $type,
                'marking_store' => isset($workflow['marking_store']['type']) ? $workflow['marking_store']['type'] : null,
            ));
            if (isset($workflow['initial_place'])) {
                $definitionDefinition->addArgument($workflow['initial_place']);
            }

            // Create MarkingStore
            if (isset($workflow['marking_store']['type'])) {
                $markingStoreDefinition = new ChildDefinition('workflow.marking_store.'.$workflow['marking_store']['type']);
                foreach ($workflow['marking_store']['arguments'] as $argument) {
                    $markingStoreDefinition->addArgument($argument);
                }
            } elseif (isset($workflow['marking_store']['service'])) {
                $markingStoreDefinition = new Reference($workflow['marking_store']['service']);
            }

            // Create Workflow
            $workflowDefinition = new ChildDefinition(sprintf('%s.abstract', $type));
            $workflowDefinition->replaceArgument(0, $definitionDefinition);
            if (isset($markingStoreDefinition)) {
                $workflowDefinition->replaceArgument(1, $markingStoreDefinition);
            }
            $workflowDefinition->replaceArgument(3, $name);

            // Store to container
            $workflowId = sprintf('%s.%s', $type, $name);
            $container->setDefinition($workflowId, $workflowDefinition);
            $container->setDefinition(sprintf('%s.definition', $workflowId), $definitionDefinition);

            // Add workflow to Registry
            if ($workflow['supports']) {
                foreach ($workflow['supports'] as $supportedClassName) {
                    $strategyDefinition = new Definition(ClassInstanceSupportStrategy::class, array($supportedClassName));
                    $strategyDefinition->setPublic(false);
                    $registryDefinition->addMethodCall('add', array(new Reference($workflowId), $strategyDefinition));
                }
            }

            // Enable the AuditTrail
            if ($workflow['audit_trail']['enabled']) {
                $listener = new Definition(AuditTrailListener::class);
                $listener->addTag('monolog.logger', array('channel' => 'workflow'));
                $listener->addTag('kernel.event_listener', array('event' => sprintf('workflow.%s.leave', $name), 'method' => 'onLeave'));
                $listener->addTag('kernel.event_listener', array('event' => sprintf('workflow.%s.transition', $name), 'method' => 'onTransition'));
                $listener->addTag('kernel.event_listener', array('event' => sprintf('workflow.%s.enter', $name), 'method' => 'onEnter'));
                $listener->addArgument(new Reference('logger'));
                $container->setDefinition(sprintf('%s.listener.audit_trail', $workflowId), $listener);
            }
        }
    }
}
