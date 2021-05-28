<?php

namespace app\helpers;


class GoogleCalendar
{
    public $api_key = 'AIzaSyCP682qZ9mv_zharuEg35BeekJFekW3nTg';
    public $calendar_id = 'indonesian@holiday.calendar.google.com';

    function getHolidayThisMonth($year, $currentMonth)
    {
        $first_day = mktime(0, 0, 0, intval($currentMonth), 1, intval($year));
        $last_day = strtotime('-1 day', mktime(0, 0, 0, intval(12) + 1, 1, intval($year)));

        $holidays_url = sprintf(
            'https://www.googleapis.com/calendar/v3/calendars/%s/events?' .
                'key=%s&timeMin=%s&timeMax=%s&maxResults=%d&orderBy=startTime&singleEvents=true',
            $this->calendar_id,
            $this->api_key,
            date('Y-m-d', $first_day) . 'T00:00:00Z',
            date('Y-m-d', $last_day) . 'T00:00:00Z',
            31
        );

        if ($results = file_get_contents($holidays_url)) {
            $results = json_decode($results);
            $holidays = array();
            foreach ($results->items as $item) {
                $date = strtotime((string)$item->start->date);
                $title = (string)$item->summary;
                $holidays[date('Y-m-d', $date)] = $title;
            }
            ksort($holidays);
        }
        return $holidays;
    }
}
