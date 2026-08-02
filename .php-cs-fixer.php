<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/App',
        __DIR__ . '/Config',
        __DIR__ . '/Routes',
        __DIR__ . '/Database',
        __DIR__ . '/Tests',
    ])
    ->exclude(['vendor', 'Storage'])
    ->name('*.php');

$config = new Config();

return $config
    ->setRules([
        '@PSR12'                                          => true,
        '@PHP84Migration'                                 => true,
        'array_syntax'                                    => ['syntax' => 'short'],
        'ordered_imports'                                 => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                               => true,
        'declare_strict_types'                            => true,
        'trailing_comma_in_multiline'                     => true,
        'phpdoc_align'                                    => ['align' => 'left'],
        'phpdoc_order'                                    => true,
        'phpdoc_scalar'                                   => true,
        'phpdoc_types'                                    => true,
        'phpdoc_var_without_name'                         => true,
        'no_superfluous_phpdoc_tags'                      => false,
        'single_quote'                                    => true,
        'binary_operator_spaces'                          => ['default' => 'align_single_space_minimal'],
        'blank_line_before_statement'                     => ['statements' => ['return', 'throw', 'try']],
        'concat_space'                                    => ['spacing' => 'one'],
        'method_argument_space'                           => ['on_multiline' => 'ensure_fully_multiline'],
        'native_function_casing'                          => true,
        'no_empty_phpdoc'                                 => true,
        'no_extra_blank_lines'                            => ['tokens' => ['extra']],
        'no_whitespace_before_comma_in_array'             => true,
        'whitespace_after_comma_in_array'                 => true,
        'yoda_style'                                      => false,
    ])
    ->setFinder($finder)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
