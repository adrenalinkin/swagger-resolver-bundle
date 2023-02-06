<?php

declare(strict_types=1);

$fileHeaderComment = <<<COMMENT
This file is part of the SwaggerResolverBundle package.

(c) Viktor Linkin <adrenalinkin@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
COMMENT;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('Resources/config')
    ->exclude('var')
    ->notPath('Tests/Functional/src/swagger.php'); // PHPCS-Fixer does not recognise the DocBlock inside swagger.php.

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        'header_comment' => ['header' => $fileHeaderComment, 'separate' => 'both'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
