<?php
declare(strict_types=1);


namespace resources\Akeneo\PimMigration;


final class ResourcesFileLocator
{
    public final static function getAbsoluteComposerJsonLocalPath(): string
    {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . 'composer.json');
    }

    public final static function getAbsoluteComposerJsonDestinationPath(): string
    {
        $path = self::getVarPath();

        return realpath($path) . DIRECTORY_SEPARATOR . 'composer.json';
    }

    public final static function getAbsoluteParametersYamlLocalPath(): string
    {
        $path = sprintf(
            '%s%sapp%sconfig%sparameters.yml',
            __DIR__,
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
