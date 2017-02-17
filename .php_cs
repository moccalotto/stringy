<?php

$package = 'Stringy';
$year = date('Y');

$header = <<<EOF
This file is part of the $package package.

@package $package
@author Kim Ravn Hansen <moccalotto@gmail.com>
@copyright $year
@license MIT
EOF;

$rules = [
    '@Symfony' => true,
    'concat_space' => ['spacing' => 'one'],
    'return_type_declaration' => ['space_before' => 'one'],
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder(PhpCsFixer\Finder::create()->in(__DIR__));

$finder = PhpCsFixer\Finder::create()
    ->in('src')
    ->in('spec');

return Config::create()
    ->finder(DefaultFinder::create()->in(__DIR__))
    ->fixers($fixers)
    ->level(FixerInterface::NONE_LEVEL)
    ->setUsingCache(true);
