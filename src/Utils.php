<?php namespace WooEvents;

class Utils
{
    /**
     * Return the values from a single column in the input array
     * @param $array array
     * @param $key   string | int
     * @return array
     */
    static function pluck($array, $key)
    {
        return array_values(array_map(function ($item) use ($key) {
            if (is_array($item))
                return $item[$key];
            else
                return $item->{$key};
        }, $array));
    }

    /**
     * If the condition is true, take the given number of items.
     * Else, return the entire array.
     * @param $condition bool
     * @param $n         int
     * @param $array     array
     * @return array
     */
    static function takeIf($condition, $n, $array)
    {
        if ($condition)
            return array_slice($array, 0, $n);
        else
            return $array;
    }
}
