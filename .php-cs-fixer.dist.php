<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([".devenv", ".direnv", "rpc", "public", "var", "vendor"])
;

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;
