# Simplicity Benchmark
A library to benchmark codes time elapsed and memory usage.

## Install
**composer**
```php 
composer require mmdm/sim-benchmark
```

Or you can simply download zip file from github and extract it, 
then put file to your project library and use it like other libraries.

## How to use
```php
// to instance a benchamrk object
$benchmark = new Benchmark();
$label = 'a label';

// start benchmark here
$benchmark->start($label);
// here you should write your algorithm
// ...
// now you can stop benchmark
$benchmark->stop($label);

//now you can see result like this
$benchmark->getReport($label);
```

## Available functions

start($label):

This method starts a benchmark for a specific label.

Note: This method just work one time for a specific label.

```php
$benchmark->start($label);
```

pause($label):

This method pause a benchmark for a specific label.

Note: If you use this method multiple times before resume method,
first call will apply.

```php
$benchmark->pause($label);
``` 

resume($label):

This method resume a benchmark for a specific label.

Note: If you use this method multiple times before pause method,
first call will apply.

```php
$benchmark->resume($label);
```

stop($label):

This method stop a benchmark for a specific label.

Note: This method just work one time for a specific label.

```php
$benchmark->stop($label);
```

getTime($label):

This method get time for a specific label.

Note: Use this function before start and stop methods, will cause 
exception.

Note: Returns time as microseconds in float format

```php
$time = $benchmark->getTime($label);
```

getMemory($label):

This method get memory for a specific label.

Note: Use this function before start and stop methods, will cause 
exception.

Note: Returns memory as bytes in int format

```php
$memory = $benchmark->getMemory($label);
```

get($label):

This method get time and memory for a specific label.

Note: Use this function before start and stop methods, will cause 
exception.

Note: Returns an array like below format

```php
array [
    'time' => $time, // 2 microsecond(s), etc.
    'memory' => $memory // 10MiB, etc.
]
```

```php
$res = $benchmark->get($label);
```

getReport($label):

This method show a report of a specific label.

Note: Use this function before start and stop methods, will cause 
exception.

Note: This method is **void**

```php
$benchmark->getReport($label);
```

# License
Under MIT license.
