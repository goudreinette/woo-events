<?php namespace WooEvents;

class Utils
{
    function formatDate($date, $time)
    {
        return date(wc_date_format(), strtotime($date)) . " " . $time;
    }
}