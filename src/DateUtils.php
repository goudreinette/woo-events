<?php namespace WooEvents;

class DateUtils
{

    /**
     * @param $previousMonths Integer
     * @param $nextMonths     Integer
     * @return \DatePeriod
     */
    function createMonthRange($previousMonths, $nextMonths)
    {
        $nextMonths    = $nextMonths + 1;
        $rangeStart    = new \DateTimeImmutable("now -$previousMonths months");
        $rangeEnd      = new \DateTimeImmutable("now +$nextMonths months");
        $monthInterval = new \DateInterval('P1M');
        $range         = new \DatePeriod($rangeStart, $monthInterval, $rangeEnd);

        return $range;
    }

    function monthRangeToArray($monthRange)
    {
        $result = [];

        foreach ($monthRange as $month) {
            array_push($result, [
                'year'      => $month->format('Y'),
                'month'     => $month->format('m'),
                'localised' => date_i18n('F', $month->getTimestamp()),
                'days'      => self::generateMonthDays($month)
            ]);
        }

        return $result;
    }

    function generateMonthDays($month)
    {
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month->format('m'), $month->format('Y'));
        $dayRange    = range(1, $daysInMonth);

        return array_chunk(array_map(function ($day) {
            return sprintf("%02d", $day);
        }, $dayRange), 7);
    }

    function formatDate($date = null)
    {
        return date('Y-m-d', strtotime($date) ?: time());
    }

    function formatTime($time = null)
    {
        return date('H:i', strtotime($time) ?: time());
    }
}

