<?php

use Sim\Benchmark\Benchmark;
use Sim\Benchmark\Exceptions\BenchmarkException;

include_once '../../vendor/autoload.php';

$benchmark = new Benchmark();
$name1 = 'md5';
$name2 = 'sha1';

$benchmark->start($name1);
$md5 = '';
for($i = 0; $i < 500000; $i++) {
    $md5 .= md5('go ');
}
$benchmark->stop($name1);
$benchmark->start($name2);
$sha1 = '';
for($i = 0; $i < 500000; $i++) {
    $sha1 .= sha1('go ');
}
$benchmark->stop($name2);

try {
    $benchmark->getReport($name1);
    $benchmark->getReport($name2);
} catch (BenchmarkException $e) {
    echo $e;
}
