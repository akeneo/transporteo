<?php
declare(strict_types=1);


namespace resources\Akeneo\PimMigration;

/**
 * Helper for test files.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright ${YEAR} Akeneo SAS (http://www.akeneo.com)
 */
final class ResourcesFileLocator
{
    public final static function getStepOneAbsoluteComposerJsonLocalPath(): string
    {
        return realpath(
            sprintf(
                '%s%s%s%s%s%s%s',
                __DIR__,
                DIRECTORY_SEPARATOR,
                'step_one_source_pim_configuration',
                DIRECTORY_SEPARATOR,
                'community_standard',
                DIRECTORY_SEPARATOR,
                'composer.json'
            )
        );
    }

    public final static function getAbsoluteComposerJsonDestinationPath(): string
    {
        $path = self::getVarPath();

        return realpath($path) . DIRECTORY_SEPARATOR . 'composer.json';
    }

    public final static function getStepOneAbsoluteParametersYamlLocalPath(): string
    {
        $path = sprintf(
            '%s%s%s%s%s%sapp%sconfig%sparameters.yml',
            __DIR__,
            DIRECTORY_SEPARATOR,
            'step_one_source_pim_configuration',
            DIRECTORY_SEPARATOR,
            'community_standard',
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        return realpath($path);
    }

    public static final function getAbsoluteParametersYamlDestinationPath(): string
    {
        $path = self::getVarPath();

        return realpath($path) . DIRECTORY_SEPARATOR . 'parameters.yml';
    }

    public final static function getStepOneAbsolutePimParametersLocalPath(): string
    {
        $path = sprintf(
            '%s%s%s%s%s%sapp%sconfig%spim_parameters.yml',
            __DIR__,
            DIRECTORY_SEPARATOR,
            'step_one_source_pim_configuration',
            DIRECTORY_SEPARATOR,
            'community_standard',
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );

        return realpath($path);
    }

    public static final function getAbsolutePimParametersDestinationPath(): string
    {
        $path = self::getVarPath();

        return realpath($path) . DIRECTORY_SEPARATOR . 'pim_parameters.yml';
    }

    public static final function getStepFolder(string $step): string
    {
        return sprintf(
            '%s%s..%sresources%s%s',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $step
        );
    }

    private static final function getVarPath(): string
    {
        return sprintf(
            '%s%s..%s..%svar',
            __DIR__,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR
        );
    }
}
