<?php

return PhpCsFixer\Config::create()
    ->setRules(array('@Symfony' => true))
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in(__DIR__ . '/src')
    );
