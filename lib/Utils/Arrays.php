<?php

namespace Utils;

Trait Arrays
{
    public static function isSequential(mixed $toTest): bool
    {
        return is_array($toTest) && self::every(
            array_keys($toTest),
            function($key, $i) {
                return $key === $i;
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public static function every(array $array, \Closure $callback, int $mod=0): bool
    {
        $handler = match($mod) {
            ARRAY_FILTER_USE_BOTH => function ($key) use ($array, $callback) {
                return $callback($array[$key], $key);
            },
            ARRAY_FILTER_USE_KEY => function ($key) use ($callback) {
                return $callback($key);
            },
            default => function ($key) use ($array, $callback) {
                return $callback($array[$key]);
            }
        };
        $keys = array_keys($array);
        $ret = true;
        for ($i = 0; $ret && $i < count($array); $i++) {
            $ret = $handler($keys[$i]) === true;
        }
        return $ret;
    }

    public static function firstKey(array $array, \Closure $callback, int $mod=0): string|int|null
    {
        $handler = match($mod) {
            ARRAY_FILTER_USE_BOTH => function ($key) use ($array, $callback) {
                return $callback($array[$key], $key);
            },
            ARRAY_FILTER_USE_KEY => function ($key) use ($callback) {
                return $callback($key);
            },
            default => function ($key) use ($array, $callback) {
                return $callback($array[$key]);
            }
        };
        $keys = array_keys($array);
        $continue = true;
        for ($i = 0; $continue && $i < count($array); $i++) {
            $key = $keys[$i];
            $continue = $handler($key) !== true;
        }
        return $continue ? null : $key;
    }
}
