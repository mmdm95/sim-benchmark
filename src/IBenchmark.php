<?php

namespace Sim\Benchmark;

interface IBenchmark
{
    /**
     * Start a time to be elapse
     *
     * @param string $name
     * @return IBenchmark
     */
    public function start(string $name): IBenchmark;

    /**
     * Pause a time that is started
     *
     * @param string $name
     * @return IBenchmark
     */
    public function pause(string $name): IBenchmark;

    /**
     * Resume a paused time
     *
     * @param string $name
     * @return IBenchmark
     */
    public function resume(string $name): IBenchmark;

    /**
     * Stop a started time
     *
     * @param string $name
     * @return IBenchmark
     */
    public function stop(string $name): IBenchmark;

    /**
     * Get elapsed time and memory usage
     *
     * @param string $name
     * @return array
     */
    public function get(string $name): array;

    /**
     * Get elapsed time and memory usage
     *
     * @param string $name
     */
    public function getReport(string $name): void;

    /**
     * Get elapsed time only
     *
     * @param string $name
     * @return float
     */
    public function getTime(string $name): float;

    /**
     * Get memory usage only
     *
     * @param string $name
     * @return int
     */
    public function getMemory(string $name): int;
}