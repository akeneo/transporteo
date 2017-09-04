<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Common;

use Akeneo\PimMigration\Domain\MigrationStep\s100_JobMigration\JobMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s110_GroupMigration\GroupMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s070_StructureMigration\StructureMigrator;
use Akeneo\PimMigration\Domain\MigrationStep\s090_SystemMigration\SystemMigrator;
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
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader as TranslationYamlFileLoader;
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
    public static function getContainer(): Container
    {
        $container = new SymfonyContainerBuilder();

        $container->addCompilerPass(new RegisterListenersPass());

        $worklowsDefinition = Yaml::parse(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'migration_tool_state_machine.yml'));

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/config'));
        $loader->load('symfony_framework.xml');

        self::loadWorkflowConfiguration($container, $worklowsDefinition['workflows']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/config'));
        $loader->load('services.yml');

        self::loadTranslatorConfiguration($container);

        $container->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('kernel.event_subscriber');

        $container->compile();

        self::loadRegistry($container, StructureMigrator::class, 'migration_tool.structure_migrator', 'addStructureMigrator');
        self::loadRegistry($container, SystemMigrator::class, 'migration_tool.system_migrator', 'addSystemMigrator');
        self::loadRegistry($container, JobMigrator::class, 'migration_tool.job_migrator', 'addJobMigrator');
        self::loadRegistry($container, GroupMigrator::class, 'migration_tool.group_migrator', 'addGroupMigrator');

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
