<?php namespace WooEvents;

class Utils
{
    static function pluck($array, $key)
    {
        return array_values(array_map(function ($item) use ($key) {
            if (is_array($item)) {
                return $item[$key];
            } else {
                return $item->{$key};
            }
        }, $array));
    }

    static function takeIf($condition, $n, $array)
    {
        if ($condition)
            return array_slice($array, 0, $n);
        else
            return $array;
    }
}