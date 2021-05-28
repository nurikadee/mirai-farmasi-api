<?php

namespace app\helpers;


use app\helpers\GoogleCalendar;

class DateHelper
{

    public static function getNationalFreeDay()
    {
        $calendar = new GoogleCalendar();
        $holiday = $calendar->getHolidayThisMonth(date('Y'), date('m'));
        $date = [];
        foreach ($holiday as $key => $value) {
            array_push($date, $key);
        }
        return $date;
    }

    public static function getTwoWeekDays()
    {
        $holiday = DateHelper::getNationalFreeDay();

        $monthDays = DateHelper::createDateRangeArray(date('Y-m-d'), date('Y-m-d', strtotime("+1 month")));


        foreach ($holiday as $free) {
            if (($key = array_search($free, $monthDays, true)) !== false) {
                unset($monthDays[$key]);
            }
        }

        $weekdays = [];
        foreach ($monthDays as $day) {
            if (date('w', strtotime($day)) == 0 || date('w', strtotime($day)) == 6) {
            } else {
                $weekdays[] = $day;
            }
        }

        return $weekdays;
    }

    public static function createDateRangeArray($strDateFrom, $strDateTo)
    {
        $aryRange = [];

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }
}
