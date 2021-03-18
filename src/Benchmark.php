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
            if (!isset($this->start_benchmarks[$name]['memory_peak'])) {
                $this->start_benchmarks[$name]['memory_peak'] = BenchmarkUtil::memory_peak_usage();
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
            $this->pause_benchmarks[$name]['memory_peak'][$this->counter[$name]] = max($this->max_memory, BenchmarkUtil::memory_peak_usage());
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
            $this->resume_benchmarks[$name]['memory_peak'][$this->counter[$name]] =
                max($this->pause_benchmarks[$name]['memory_peak'][$this->counter[$name]],
                    BenchmarkUtil::memory_peak_usage());
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
            if (!isset($this->stop_benchmarks[$name]['memory_peak'])) {
                if (isset($this->resume_benchmarks[$name]['memory_peak'])) {
                    $memoryPeak = max($this->resume_benchmarks[$name]['memory_peak'][$this->counter[$name] - 1],
                        BenchmarkUtil::memory_peak_usage());
                } else {
                    $memoryPeak = max($this->start_benchmarks[$name]['memory_peak'],
                        BenchmarkUtil::memory_peak_usage());
                }
                $this->stop_benchmarks[$name]['memory_peak'] = $memoryPeak;
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
        $memoryPeak = BenchmarkUtil::memoryUnitNNumber($this->getMemoryPeak($name));
        $time = BenchmarkUtil::timeUnitNNumber($this->getTime($name));
        return [
            'time' => $time['number'] . ' ' . $time['unit'],
            'memory' => $memory['number'] . $memory['unit'],
            'memory_peak' => $memoryPeak['number'] . $memoryPeak['unit'],
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
        $space = ' ';
        echo "<pre>";
        echo "<br>";
        echo str_repeat($sep, ((strlen($name) + strlen($report['time']) + strlen($report['memory']) + strlen($report['memory_peak'])) * 2) + (38));
        echo "<br>";
        echo '|' . str_repeat($space, ((strlen($name) + strlen($report['time']) + strlen($report['memory']) + strlen($report['memory_peak'])) * 2) + (36)) . '|';
        echo "<br>";
        echo '|' . str_repeat($space, strlen($name)) . ' name ' . str_repeat($space, strlen($name)) . '|';
        echo str_repeat($space, strlen($report['time'])) . ' time ' . str_repeat($space, strlen($report['time'])) . '|';
        echo str_repeat($space, strlen($report['memory'])) . ' memory ' . str_repeat($space, strlen($report['memory'])) . '|';
        echo str_repeat($space, strlen($report['memory_peak'])) . ' memory peak ' . str_repeat($space, strlen($report['memory_peak'])) . '|';
        echo "<br>";
        echo '|' . str_repeat($sep, ((strlen($name) + strlen($report['time']) + strlen($report['memory']) + strlen($report['memory_peak'])) * 2) + (36)) . '|';
        echo "<br>";
        echo '|' . str_repeat($space, ((strlen($name) + strlen($report['time']) + strlen($report['memory']) + strlen($report['memory_peak'])) * 2) + (36)) . '|';
        echo "<br>";
        echo '|' . $space . $name . str_repeat($space, strlen($name) + 5) . '|' . $space;
        echo $report['time'] . str_repeat($space, strlen($report['time']) + 5) . '|' . $space;
        echo $report['memory'] . str_repeat($space, strlen($report['memory']) + 7) . '|' . $space;
        echo $report['memory_peak'] . str_repeat($space, strlen($report['memory_peak']) + 12) . '|';
        echo "<br>";
        echo '|' . str_repeat($sep, ((strlen($name) + strlen($report['time']) + strlen($report['memory']) + strlen($report['memory_peak'])) * 2) + (36)) . '|';
        echo "<br>";
        echo "</pre>";
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
     * {@inheritdoc}
     * @throws BenchmarkException
     */
    public function getMemoryPeak(string $name): int
    {
        $start = ($this->start_benchmarks[$name]['memory_peak']) ?? null;
        $stop = ($this->stop_benchmarks[$name]['memory_peak']) ?? null;
        $pause = ($this->pause_benchmarks[$name]['memory_peak']) ?? null;
        $resume = ($this->resume_benchmarks[$name]['memory_peak']) ?? null;

        if (is_null($start) || is_null($stop)) {
            throw new BenchmarkException("Benchmark memory peak does not start/stop");
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
        $memoryPeak = ($stop - $resumeLastMemory) + $pausedMemory + ($pausedFirstMemory - $start);

        return $memoryPeak;
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