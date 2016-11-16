<?php namespace WooEvents;

class Utils
{
    static function formatDate($date, $time)
    {
        return date(wc_date_format(), strtotime($date)) . " " . $time;
    }

    static function pluck($array, $key)
    {
        return array_map(function ($item) use ($key) {
            if (is_array($item)) {
                return $item[$key];
            } else {
                return $item->{$key};
            }
        }, $array);
    }
}