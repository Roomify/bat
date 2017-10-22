<?php

/**
 * @file
 * Class EventItemizer
 */

namespace Roomify\Bat\Event;

use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\Unit\Unit;

/**
 * The EventItemizer class does the hard work of splitting an event into discrete time
 * units in the following structure
 * [BAT_DAY][year][month][day][value]
 * [BAT_HOUR][year][month][day][hour][value]
 * [BAT_MINUTE][year][month][hour][minute][value]
 *
 * This data structure allows to quickly retrieve the state of a unit for a provided time
 * period. The value is either the default value of the unit or the value that the event
 * introduced. If the value in either BAT_DAY or BAT_HOUR is -1 it means that that specific
 * day or that specific hour are non-determinant. This means that in order to determine the
 * value of the event for that point in time we need to look at a lower level of granularity.
 *
 * Example - consider breaking up the following event
 *
 * start-date: 2016-01-01 1210
 * end-date: 2016-01-03 1210
 * value: 10
 *
 * [BAT_DAY][2016][01][d1][-1] - The first day starts at 1210 so the DAY array is not enough
 * [BAT_DAY][2016][01][d2][10] - The second day is a full day at the same value of 10
 * [BAT_DAY][2016][01][d3][-1] - The last day is no a full day so the day array in non-determinant
 * [BAT_HOUR][2016][01][d1][h12][-1] - The first hour of the event starts at 10 minutes so the hour is non-determinant
 * [BAT_HOUR][2016][01][d1][h13][10]
 * [BAT_HOUR][2016][01][d1][h14][10]
 * [BAT_HOUR][2016][01][d1][h15][10]
 * [BAT_HOUR][2016][01][d1][h16][10]
 * [BAT_HOUR][2016][01][d1][h17][10]
 * [BAT_HOUR][2016][01][d1][h18][10]
 * [BAT_HOUR][2016][01][d1][h19][10]
 * [BAT_HOUR][2016][01][d1][h20][10]
 * [BAT_HOUR][2016][01][d1][h21][10]
 * [BAT_HOUR][2016][01][d1][h22][10]
 * [BAT_HOUR][2016][01][d1][h23][10]
 *                                    - we don't need to state anything about hours on the 2nd of Jan since the day array is determinant
 * [BAT_HOUR][2016][01][d3][h01][10]
 * [BAT_HOUR][2016][01][d3][h02][10]
 * [BAT_HOUR][2016][01][d3][h03][10]
 * [BAT_HOUR][2016][01][d3][h04][10]
 * [BAT_HOUR][2016][01][d3][h05][10]
 * [BAT_HOUR][2016][01][d3][h06][10]
 * [BAT_HOUR][2016][01][d3][h07][10]
 * [BAT_HOUR][2016][01][d3][h08][10]
 * [BAT_HOUR][2016][01][d3][h09][10]
 * [BAT_HOUR][2016][01][d3][h10][10]
 * [BAT_HOUR][2016][01][d3][h11][10]
 * [BAT_HOUR][2016][01][d3][h12][-1] - The last hour of the event ends at the 10th minute so will need to minute array
 *
 * [BAT_MINUTE][2016][01][d1][h12][m00][10] - Minutes, which is the maximum granularity, are always determinant
 * [BAT_MINUTE][2016][01][d1][h12][m01][10]
 * [BAT_MINUTE][2016][01][d1][h12][m02][10]
 * [BAT_MINUTE][2016][01][d1][h12][m03][10]
 * [BAT_MINUTE][2016][01][d1][h12][m04][10]
 * [BAT_MINUTE][2016][01][d1][h12][m05][10]
 * [BAT_MINUTE][2016][01][d1][h12][m06][10]
 * [BAT_MINUTE][2016][01][d1][h12][m07][10]
 * [BAT_MINUTE][2016][01][d1][h12][m08][10]
 * [BAT_MINUTE][2016][01][d1][h12][m09][10]
 * [BAT_MINUTE][2016][01][d1][h12][m10][10]
 *
 * [BAT_MINUTE][2016][01][d3][h12][m00][10]
 * [BAT_MINUTE][2016][01][d3][h12][m01][10]
 * [BAT_MINUTE][2016][01][d3][h12][m02][10]
 * [BAT_MINUTE][2016][01][d3][h12][m03][10]
 * [BAT_MINUTE][2016][01][d3][h12][m04][10]
 * [BAT_MINUTE][2016][01][d3][h12][m05][10]
 * [BAT_MINUTE][2016][01][d3][h12][m06][10]
 * [BAT_MINUTE][2016][01][d3][h12][m07][10]
 * [BAT_MINUTE][2016][01][d3][h12][m08][10]
 * [BAT_MINUTE][2016][01][d3][h12][m09][10]
 * [BAT_MINUTE][2016][01][d3][h12][m10][10]
 *
 * Class EventItemizer
 * @package Roomify\Bat\Event
 */
class EventItemizer {

  const BAT_DAY = 'bat_day';
  const BAT_HOUR = 'bat_hour';
  const BAT_MINUTE = 'bat_minute';
  const BAT_HOURLY = 'bat_hourly';
  const BAT_DAILY = 'bat_daily';

  /**
   * @var \Roomify\Bat\Event\EventInterface
   */
  protected $event;

  /**
   * @var string
   */
  protected $granularity;

  public function __construct(EventInterface $event, $granularity = EventItemizer::BAT_HOURLY) {
    $this->event = $event;
    $this->granularity = $granularity;
  }

  /**
   * Transforms the event in a breakdown of days, hours and minutes with associated states.
   *
   * @return array
   */
  public function itemizeEvent() {
    // In order to itemize the event we cycle through each day of the event and determine
    // what should go in the DAY array to start with. While we could use P1M this created
    // problems with months like February (because the period is 30 days) so stepping through
    // each day is safer.
    $interval = new \DateInterval('P1D');

    // Set the end date to the last day of the month so that we are sure to get that last month unless
    // we are already dealing with the last day of the month
    if ($this->event->getEndDate()->format('d') != $this->event->getEndDate()->format('t')) {
      $adjusted_end_day = new \DateTime($this->event->getEndDate()->format('Y-n-t'));
    }
    // Deal with the special case of last day of month and daily granularity where the DatePeriod will not indicate one day unless the time is slightly different
    // We add a minute to compensate
    elseif (($this->event->getStartDate()->format('Y-m-d H:i') == $this->event->getEndDate()->format('Y-m-d H:i')) && $this->granularity == EventItemizer::BAT_DAILY) {
      $adjusted_end_day = new \DateTime($this->event->getEndDate()->add(new \DateInterval('PT1M'))->format('Y-m-d H:i'));
    }
    else {
      $adjusted_end_day = new \DateTime($this->event->getEndDate()->format('Y-m-d H:i'));
    }

    $daterange = new \DatePeriod($this->event->getStartDate(), $interval, $adjusted_end_day);

    $itemized = array();

    $old_month = $this->event->getStartDate()->format('Y-n');

    $start = TRUE;

    // Cycle through each month
    foreach ($daterange as $date) {

      // Check if we have
      if (($date->format('Y-n') != $old_month) || ($start)) {

        $year = $date->format("Y");
        $dayinterval = new \DateInterval('P1D');

        // Handle the first month
        if ($this->event->isFirstMonth($date)) {
          // If we are in the same month the end date is the end date of the event
          if ($this->event->isSameMonth()) {
            $dayrange = new \DatePeriod($this->event->getStartDate(), $dayinterval, new \DateTime($this->event->getEndDate()->format("Y-n-j 23:59:59")));
          } else { // alternatively it is the last day of the start month
            $dayrange = new \DatePeriod($this->event->getStartDate(), $dayinterval, $this->event->endMonthDate($this->event->getStartDate()));
          }
          foreach ($dayrange as $day) {
            $itemized[EventItemizer::BAT_DAY][$year][$day->format('n')]['d' . $day->format('j')] = $this->event->getValue();
          }
        }

        // Handle the last month (will be skipped if event is same month)
        elseif ($this->event->isLastMonth($date)) {
          $dayrange = new \DatePeriod(new \DateTime($date->format("Y-n-1")), $dayinterval, $this->event->getEndDate());
          foreach ($dayrange as $day) {
            $itemized[EventItemizer::BAT_DAY][$year][$day->format('n')]['d' . $day->format('j')] = $this->event->getValue();
          }
        }

        // We are in an in-between month - just cycle through and set dates (time on end date set to ensure it is included)
        else {
          $dayrange = new \DatePeriod(new \DateTime($date->format("Y-n-1")), $dayinterval, new \DateTime($date->format("Y-n-t 23:59:59")));
          foreach ($dayrange as $day) {
            $itemized[EventItemizer::BAT_DAY][$year][$day->format('n')]['d' . $day->format('j')] = $this->event->getValue();
          }
        }
      }
      $start = FALSE;
      $old_month = $date->format('Y-n');
    }

    if ($this->granularity == EventItemizer::BAT_HOURLY) {
      // Add granural info in
      $itemized = $this->createDayGranural($itemized);
    }

    return $itemized;
  }

  /**
   * Based on the start and end dates of the event it creates the appropriate granular events
   * and adds them to an array suitable for manipulating easily or storing in the database.
   *
   * @param array $itemized
   * @return array
   */
  private function createDayGranural($itemized = array()) {
    $interval = new \DateInterval('PT1M');

    $sy = $this->event->getStartDate()->format('Y');
    $sm = $this->event->getStartDate()->format('n');
    $sd = $this->event->getStartDate()->format('j');

    $ey = $this->event->getEndDate()->format('Y');
    $em = $this->event->getEndDate()->format('n');
    $ed = $this->event->getEndDate()->format('j');

    // Clone the dates otherwise changes will change the event dates themselves
    $start_date = clone($this->event->getStartDate());
    $end_date = clone($this->event->getEndDate());

    if ($this->event->isSameDay()) {
      if (!($this->event->getStartDate()->format('H:i') == '00:00' && $this->event->getEndDate()->format('H:i') == '23:59')) {
        $itemized_same_day = $this->createHourlyGranular($start_date, $end_date->add(new \DateInterval('PT1M')), $interval);
        $itemized[EventItemizer::BAT_DAY][$sy][$sm]['d' . $sd] = -1;
        $itemized[EventItemizer::BAT_HOUR][$sy][$sm]['d' . $sd] = $itemized_same_day[EventItemizer::BAT_HOUR][$sy][$sm]['d' . $sd];
        $itemized[EventItemizer::BAT_MINUTE][$sy][$sm]['d' . $sd] = $itemized_same_day[EventItemizer::BAT_MINUTE][$sy][$sm]['d' . $sd];
      }
    }
    else {
      // Deal with the start day unless it starts on midnight precisely at which point the whole day is booked
      if (!($this->event->getStartDate()->format('H:i') == '00:00')) {
        $itemized_start = $this->createHourlyGranular($start_date, new \DateTime($start_date->format("Y-n-j 23:59:59")), $interval);
        $itemized[EventItemizer::BAT_DAY][$sy][$sm]['d' . $sd] = -1;
        $itemized[EventItemizer::BAT_HOUR][$sy][$sm]['d' . $sd] = $itemized_start[EventItemizer::BAT_HOUR][$sy][$sm]['d' . $sd];
        $itemized[EventItemizer::BAT_MINUTE][$sy][$sm]['d' . $sd] = $itemized_start[EventItemizer::BAT_MINUTE][$sy][$sm]['d' . $sd];
      }
      else {
        // Just set an empty hour and minute
        $itemized[EventItemizer::BAT_HOUR][$sy][$sm]['d' . $sd] = array();
        $itemized[EventItemizer::BAT_MINUTE][$sy][$sm]['d' . $sd] = array();
      }

      // Deal with the end date unless it ends just before midnight at which point we don't need to go further
      if ($this->event->getEndDate()->format('H:i') == '23:59') {
        $itemized[EventItemizer::BAT_HOUR][$ey][$em]['d' . $ed] = array();
        $itemized[EventItemizer::BAT_MINUTE][$ey][$em]['d' . $ed] = array();
      }
      else {
        $itemized_end = $this->createHourlyGranular(new \DateTime($end_date->format("Y-n-j 00:00:00")), $end_date->add(new \DateInterval('PT1M')), $interval);
        $itemized[EventItemizer::BAT_DAY][$ey][$em]['d' . $ed] = -1;
        $itemized[EventItemizer::BAT_HOUR][$ey][$em]['d' . $ed] = $itemized_end[EventItemizer::BAT_HOUR][$ey][$em]['d' . $ed];
        $itemized[EventItemizer::BAT_MINUTE][$ey][$em]['d' . $ed] = $itemized_end[EventItemizer::BAT_MINUTE][$ey][$em]['d' . $ed];
      }
    }

    return $itemized;
  }

  /**
   * Given a DatePeriod it transforms it in hours and minutes. Used to break the first and
   * last days of an event into more granular events.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param \DateInterval $interval
   * @return array
   */
  public function createHourlyGranular(\DateTime $start_date, \DateTime $end_date, \DateInterval $interval) {
    $period = new \DatePeriod($start_date, $interval, $end_date);

    $itemized = array();

    $start_minute = (int)$start_date->format('i');

    $event_value = $this->event->getValue();

    $year = $start_date->format('Y');
    $month = $start_date->format('n');
    $day = $start_date->format('j');
    $hour = $start_date->format('G');
    $min = $start_date->format('i');

    foreach ($period as $minute) {
      // Re-calculate if we're at a day boundary.
      if ($hour == 24) {
        $year = $minute->format('Y');
        $month = $minute->format('n');
        $day = $minute->format('j');
        $hour = $minute->format('G');
        $min = $minute->format('i');
      }

      // Doing minutes so set the values in the minute array
      $itemized[EventItemizer::BAT_MINUTE][$year][$month]['d' . $day]['h' . $hour]['m' . $min] = $event_value;
      // Let the hours know that it cannot determine availability
      $itemized[EventItemizer::BAT_HOUR][$year][$month]['d' . $day]['h' . $hour] = -1;
      $min++;

      if ($min == 60 && $start_minute !== 0) {
        // Not a real hour - leave as is and move on
        $min = 0;
        $hour++;
        $start_minute = 0;
      }
      elseif ($min == 60 && $start_minute == 0) {
        // Did a real whole hour so initialize the hour
        $itemized[EventItemizer::BAT_HOUR][$year][$month]['d' . $day]['h' . $hour] = $event_value;

        $min = 0;
        $hour++;
        $start_minute = 0;
      }

      $min = str_pad($min, 2, 0, STR_PAD_LEFT);
    }

    // Daylight Saving Time
    $timezone = new \DateTimeZone(date_default_timezone_get());
    $transitions = $timezone->getTransitions($start_date->getTimestamp(), $end_date->getTimestamp());

    unset($transitions[0]);
    foreach ($transitions as $transition) {
      if ($transition['isdst']) {
        $date = new \DateTime();
        $date->setTimestamp($transition['ts']);

        $hour = $date->format('G');
        for ($i = 0; $i < 60; $i++) {
          $minute = ($i < 10) ? '0' . $i : $i;

          $itemized[EventItemizer::BAT_MINUTE][$date->format('Y')][$date->format('n')]['d' . $date->format('j')]['h' . $hour]['m' . $minute] = $this->event->getValue();
        }
      }
    }

    return $itemized;
  }

}
