<?php
require_once __DIR__ . '/../vendor/autoload.php';

$generator = new \PWGen\PasswordGenerator();
echo $generator->generate() . "\n";
