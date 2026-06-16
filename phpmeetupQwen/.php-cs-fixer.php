<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@PHP80Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_whitespace_in_blank_line' => true,
        'concat_space' => ['spacing' => 'one'],
        'blank_line_before_statement' => [
            'statements' => ['declare'],
        ],
    ])
    ->setFinder($finder)
;