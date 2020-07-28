<?php

use Sim\Benchmark\Benchmark;
use Sim\Benchmark\Exceptions\BenchmarkException;

include_once '../../vendor/autoload.php';

$benchmark = new Benchmark();
$name = 'str';
$name2 = 'notImportant';
$name3 = 'both';

//$t1 = microtime(true);
//$m1 = memory_get_usage(true);

$benchmark->start($name3);
$benchmark->start($name);
$str = '';
for($i = 0; $i < 500000; $i++) {
    $str .= 'hi1 ';
}
$benchmark->pause($name);

$benchmark->start($name2);
$str2 = '';
for($i = 0; $i < 5000000; $i++) {
    $str2 .= 'not important ';
}
$benchmark->stop($name2);

$benchmark->resume($name);
for($i = 0; $i < 5000000; $i++) {
    $str .= 'hi2 ';
}
$benchmark->stop($name);
$benchmark->stop($name3);

//var_dump(microtime(true) - $t1);
//var_dump(memory_get_usage(true) - $m1);

try {
    $benchmark->getReport($name);
    $benchmark->getReport($name2);
    $benchmark->getReport($name3);
} catch (BenchmarkException $e) {
    echo $e;
}