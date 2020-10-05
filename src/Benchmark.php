<?php
declare(ticks=1);

namespace Sim\Benchmark;

use Sim\Benchmark\Exceptions\BenchmarkException;
use Sim\Benchmark\Utils\BenchmarkUtil;

class Benchmark implements IBenchmark
{
    /**
     * @var array $start_benchmarks
     */
    protected $start_benchmarks = [];

    /**
     * @var array $stop_benchmarks
     */
    protected $stop_benchmarks = [];

    /**
     * @var array $pause_benchmarks
     */
    protected $pause_benchmarks = [];

    /**
     * @var array $resume_benchmarks
     */
    protected $resume_benchmarks = [];

    /**
     * @var int $max_memory
     */
    protected $max_memory = 0;

    /**
     * @var array $counter
     */
    protected $counter = [];

    /**
     * @var string $default_id
     */
    protected $default_id = 'default';

    /**
     * {@inheritdoc}
     */
    public function start(string $name): IBenchmark
    {
        $this->reset();
        if (!isset($this->start_benchmarks[$name])) {
            $this->counter[$name] = 0;
            $this->start_benchmarks[$name]['time'] = microtime(true);
            if (!isset($this->start_benchmarks[$name]['memory'])) {
                $this->start_benchmarks[$name]['memory'] = BenchmarkUtil::memory_usage();
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function pause(string $name): IBenchmark
    {
        // remove tick function from each tick
        unregister_tick_function([$this, 'doTick']);
        if (isset($this->start_benchmarks[$name]['time']) &&
            !isset($this->pause_benchmarks[$name]['time'][$this->counter[$name]])) {
            $this->pause_benchmarks[$name]['time'][$this->counter[$name]] = microtime(true);
            $this->pause_benchmarks[$name]['memory'][$this->counter[$name]] = max($this->max_memory, BenchmarkUtil::memory_usage());
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resume(string $name): IBenchmark
    {
        if (isset($this->pause_benchmarks[$name]['time'][$this->counter[$name]]) &&
            !isset($this->resume_benchmarks[$name]['time'][$this->counter[$name]])) {
            $this->reset();
            $this->resume_benchmarks[$name]['time'][$this->counter[$name]] = microtime(true);
            $this->resume_benchmarks[$name]['memory'][$this->counter[$name]] =
                max($this->pause_benchmarks[$name]['memory'][$this->counter[$name]],
                    BenchmarkUtil::memory_usage());
            $this->counter[$name]++;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function stop(string $name): IBenchmark
    {
        // remove tick function from each tick
        unregister_tick_function([$this, 'doTick']);
        if (!isset($this->stop_benchmarks[$name]['time'])) {
            $this->stop_benchmarks[$name]['time'] = microtime(true);
            if (!isset($this->stop_benchmarks[$name]['memory'])) {
                if (isset($this->resume_benchmarks[$name]['memory'])) {
                    $memory = max($this->resume_benchmarks[$name]['memory'][$this->counter[$name] - 1],
                        BenchmarkUtil::memory_usage());
                } else {
                    $memory = max($this->start_benchmarks[$name]['memory'],
                        BenchmarkUtil::memory_usage());
                }
                $this->stop_benchmarks[$name]['memory'] = $memory;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     * @throws BenchmarkException
     */
    public function get(string $name): array
    {
        $memory = BenchmarkUtil::memoryUnitNNumber($this->getMemory($name));
        $time = BenchmarkUtil::timeUnitNNumber($this->getTime($name));
        return [
            'time' => $time['number'] . ' ' . $time['unit'],
            'memory' => $memory['number'] . $memory['unit'],
        ];
    }

    /**
     * {@inheritdoc}
     * @throws BenchmarkException
     */
    public function getReport(string $name): void
    {
        $report = $this->get($name);
        $sep = '_';
        echo PHP_EOL;
        echo str_repeat($sep, ((strlen($name) + strlen($report['time']) + strlen($report['memory'])) * 2) + (24));
        echo PHP_EOL;
        echo '|' . str_repeat(' ', ((strlen($name) + strlen($report['time']) + strlen($report['memory'])) * 2) + (22)) . '|';
        echo PHP_EOL;
        echo '|' . str_repeat(' ', strlen($name)) . ' name ' . str_repeat(' ', strlen($name)) . '|';
        echo str_repeat(' ', strlen($report['time'])) . ' time ' . str_repeat(' ', strlen($report['time'])) . '|';
        echo str_repeat(' ', strlen($report['memory'])) . ' memory ' . str_repeat(' ', strlen($report['memory'])) . '|';
        echo PHP_EOL;
        echo '|' . str_repeat($sep, ((strlen($name) + strlen($report['time']) + strlen($report['memory'])) * 2) + (22)) . '|';
        echo PHP_EOL;
        echo '|' . str_repeat(' ', ((strlen($name) + strlen($report['time']) + strlen($report['memory'])) * 2) + (22)) . '|';
        echo PHP_EOL;
        echo '| ' . $name . str_repeat(' ', strlen($name) + 5) . '| ';
        echo $report['time'] . str_repeat(' ', strlen($report['time']) + 5) . '| ';
        echo $report['memory'] . str_repeat(' ', strlen($report['memory']) + 7) . '|';
        echo PHP_EOL;
        echo '|' . str_repeat($sep, ((strlen($name) + strlen($report['time']) + strlen($report['memory'])) * 2) + (22)) . '|';
        echo PHP_EOL;
    }

    /**
     * {@inheritdoc}
     * @throws BenchmarkException
     */
    public function getTime(string $name): float
    {
        $start = ($this->start_benchmarks[$name]['time']) ?? null;
        $stop = ($this->stop_benchmarks[$name]['time']) ?? null;
        $pause = ($this->pause_benchmarks[$name]['time']) ?? null;
        $resume = ($this->resume_benchmarks[$name]['time']) ?? null;

        if (is_null($start) || is_null($stop)) {
            throw new BenchmarkException("Benchmark timer does not start/stop");
        }

        $pausedTime = 0;
        if (!is_null($pause) && !is_null($resume) && 0 != count($pause) && count($pause) == count($resume)) {
            foreach ($pause as $c => $t) { // $counter => $time
                $pausedTime += $resume[$c] - $t;
            }
        }
        $time = $stop - $start - $pausedTime;

        return $time;
    }

    /**
     * {@inheritdoc}
     * @throws BenchmarkException
     */
    public function getMemory(string $name): int
    {
        $start = ($this->start_benchmarks[$name]['memory']) ?? null;
        $stop = ($this->stop_benchmarks[$name]['memory']) ?? null;
        $pause = ($this->pause_benchmarks[$name]['memory']) ?? null;
        $resume = ($this->resume_benchmarks[$name]['memory']) ?? null;

        if (is_null($start) || is_null($stop)) {
            throw new BenchmarkException("Benchmark memory does not start/stop");
        }

        $pausedMemory = 0;
        $pausedFirstMemory = 0;
        $resumeLastMemory = 0;
        if (!is_null($pause) && !is_null($resume) && 0 != count($pause) && count($pause) == count($resume)) {
            $pausedFirstMemory = array_shift($pause);
            $resumeLastMemory = array_pop($resume);
            foreach ($pause as $c => $m) { // $counter => $memory
                $pausedMemory += $resume[$c - 1] - $m;
            }
        }
        $memory = ($stop - $resumeLastMemory) + $pausedMemory + ($pausedFirstMemory - $start);

        return $memory;
    }

    /**
     * Tick function to get memory
     */
    protected function doTick()
    {
        $this->max_memory = max($this->max_memory, memory_get_usage(true));
    }

    /**
     * Reset initial values
     */
    protected function reset()
    {
        $this->max_memory = 0;
        // clear all garbage
        gc_collect_cycles();
        // starts a tick function for each tick
        register_tick_function([$this, 'doTick']);
    }
}