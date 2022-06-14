<?php

use App\Helpers\FileSystemFetcher;

include 'vendor/autoload.php';

$fileLocation = '/parser_test';
if(isset($argv[1])) {
    $fileLocation = $argv[1];
}
$app = new FileSystemFetcher($fileLocation);
$app->exec();