<?php

$dirs = ['var', 'vendor'];
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude($dirs);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => ['default' => 'align'],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
