<?php
// PHP Coding Standards Fixer
// https://cs.symfony.com/
// https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/config.rst

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('build')
    ->exclude('vendor')
;

return (new PhpCsFixer\Config())
    ->setUnsupportedPhpVersionAllowed(true)
    ->setRules([
        // '@auto' => true,
        // '@PHP7x3Migration' => true,
        // '@PSR1' => true,
        // '@PSR2' => true,
        // '@PSR12' => true,
        // '@Symfony' => true,
        // 'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        // 'strict_param' => true,
    ])
    ->setFinder($finder)
    ;

?>
