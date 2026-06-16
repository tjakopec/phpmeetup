<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')    // Ovdje stavi direktorije koje želiš skenirati
    ->in(__DIR__ . '/tests')  // (npr. 'src', 'tests', 'config')
    ->exclude('var')          // Isključi direktorije koje ne želiš dirati
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true, // Ovo je odličan set pravila za početak (uključuje PSR-12)
        'array_syntax' => ['syntax' => 'short'], // Forsira [] umjesto array()
        'yoda_style' => false, // Isključuje Yoda uvjete (npr. if (null === $var)) ako ih ne voliš
    ])
    ->setFinder($finder)
;