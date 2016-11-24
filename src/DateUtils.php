<?php namespace WooEvents;


class DateUtils
{
    /**
     * @param $previousMonths Integer
     * @param $nextMonths     Integer
     * @return \DatePeriod
     */
    public static function createMonthRange($previousMonths, $nextMonths)
    {
        $nextMonths    = $nextMonths + 1;
        $rangeStart    = new \DateTimeImmutable("now -$previousMonths months");
        $rangeEnd      = new \DateTimeImmutable("now +$nextMonths months");
        $monthInterval = new \DateInterval('P1M');
        $range         = new \DatePeriod($rangeStart, $monthInterval, $rangeEnd);

        return $range;
    }

    public static function monthRangeToArray($monthRange)
    {
        $result = [];

        foreach ($monthRange as $month) {
            array_push($result, [
                'year'      => $month->format('Y'),
                'month'     => $month->format('m'),
                'localised' => date_i18n('F', $month->getTimestamp()),
                'days'      => array_chunk(range(1, cal_days_in_month(CAL_GREGORIAN, $month->format('m'), $month->format('Y'))), 7)
            ]);
        }

        return $result;
    }

    public static function formatDate($date = null)
    {
        return date('Y-m-d', strtotime($date) ?: time());
    }

    public static function formatTime($time = null)
    {
        return date('H:i', strtotime($time) ?: time());
    }
}