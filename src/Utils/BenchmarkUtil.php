<?php

namespace Sim\Benchmark\Utils;

class BenchmarkUtil
{
    /**
     * @see http://fr2.php.net/manual/en/function.mb-convert-encoding.php#103300
     * @see https://gist.github.com/real34/1330072
     *
     * @return int
     */
    public static function memory_usage(): int
    {
        return memory_get_usage(true);
    }

    /**
     * @see https://alexwebdevelop.com/monitor-script-memory-usage/#:~:text=A%20PHP%20script%20can%20use,to%20the%20script's%20memory%20usage.
     *
     * @return int
     */
    public static function memory_limit()
    {
        $limit_string = ini_get('memory_limit');
        $unit = strtolower(mb_substr($limit_string, -1));
        $bytes = intval(mb_substr($limit_string, 0, -1), 10);

        switch ($unit) {
            case 'k':
                $bytes *= 1024;
                break 1;
            case 'm':
                $bytes *= 1048576;
                break 1;
            case 'g':
                $bytes *= 1073741824;
                break 1;
            default:
                break 1;
        }

        return $bytes;
    }

    public static function timeUnitNNumber($microseconds): array
    {
        $unit = ['microsecond(s)', 'millisecond(s)', 'second(s)', 'minute(s)', 'hour(s)'];
        if (0 == $microseconds) {
            $number = $microseconds;
            $unit = $unit[0];
        } else {
            $number = $microseconds;
            $i = 0;
            if ($number > 1000) {
                $number *= 1000;
                $i++;
                if ($number > 1000) {
                    $number *= 1000;
                    var_dump($number);
                    $i++;
                    if ($number > 60) {
                        $number /= 60;
                        $i++;
                        if ($number > 60) {
                            $number /= 60;
                            $i++;
                        }
                    }
                }
            }
            $number = self::round($number);
            $unit = $unit[$i];
        }

        return [
            'number' => $number,
            'unit' => $unit,
        ];
    }

    /**
     * @see https://www.php.net/manual/en/function.memory-get-usage.php#96280
     *
     * @param $bytes
     * @param bool $binary_prefix
     * @return array
     */
    public static function memoryUnitNNumber($bytes, $binary_prefix = true): array
    {
        if ($binary_prefix) {
            $unit = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
            if (0 == $bytes) {
                $number = 0;
                $unit = $unit[0];
            } else {
                $number = self::round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))));
                $unit = isset($unit[(int)$i]) ? $unit[(int)$i] : 'B';
            }
        } else {
            $unit = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
            if (0 == $bytes) {
                $number = 0;
                $unit = $unit[0];
            } else {
                $number = self::round($bytes / pow(1000, ($i = floor(log($bytes, 1000)))));
                $unit = isset($unit[(int)$i]) ? $unit[(int)$i] : 'B';
            }
        }

        return [
            'number' => $number,
            'unit' => $unit,
        ];
    }

    /**
     * @param $number
     * @param int $decimal_points
     * @return float
     */
    public static function round($number, $decimal_points = 2): float
    {
        return round($number, $decimal_points);
    }
}