<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$rules = [
    '@PSR12' => true,
    'ordered_imports' => true,
    'no_unused_imports' => true,
    'no_mixed_echo_print' => ['use' => 'print'],
    'increment_style' => ['style' => 'post'],
    'yoda_style' => false,
    'concat_space' => ['spacing' => 'one'],
    'method_chaining_indentation' => true,
    'php_unit_test_class_requires_covers' => false,
    'array_indentation' => true,
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/config',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new Config())
    ->setParallelConfig(ParallelConfigFactory::detect())
    ->setRules($rules)
    ->setFinder($finder)
    ->setRiskyAllowed(false)
    ->setUsingCache(true)
;
