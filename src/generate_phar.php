<?php
$lib_path = __DIR__ . "/../lib/";
$filename = ($argc>1) ? $argv[1] : "plodis.phar";

$stub = <<< EOF
<?php
Phar::mapPhar();
include 'phar://plodis.phar/Plodis.php';
__HALT_COMPILER();
EOF;

$phar = new Phar($filename);
$phar->compress(Phar::NONE);
$phar->setStub($stub);
$phar->buildFromDirectory($lib_path);