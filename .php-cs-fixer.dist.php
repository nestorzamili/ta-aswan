<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/app')
    ->exclude('Views')
    ->exclude('Config')
    ->exclude('Database/Migrations')
    ->name('*.php')
    ->notName('*.blade.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                       => true,
        'array_syntax'                 => ['syntax' => 'short'],
        'binary_operator_spaces'       => [
            'default'   => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal',
                '='  => 'align_single_space_minimal',
            ],
        ],
        'no_unused_imports'            => true,
        'ordered_imports'              => ['sort_algorithm' => 'alpha'],
        'single_quote'                 => true,
        'trailing_comma_in_multiline'  => ['elements' => ['arrays', 'arguments', 'parameters']],
        'no_trailing_whitespace'       => true,
        'blank_line_after_namespace'   => true,
        'method_argument_space'        => ['on_multiline' => 'ensure_fully_multiline'],
        'concat_space'                 => ['spacing' => 'one'],
    ])
    ->setFinder($finder);
