<?php

/**
 * @file
 * Class CheckInDayConstraint
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Calendar\CalendarResponse;
use Roomify\Bat\Constraint\Constraint;

/**
 *
 */
class CheckInDayConstraint extends Constraint {

  /**
   * @var
   */
  protected $checkin_day;

  /**
   * @param $units
   * @param $checkin_day
   */
  public function __construct($units, $checkin_day, $start_date = NULL, $end_date = NULL) {
    parent::__construct($units);

    $this->checkin_day = $checkin_day;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function applyConstraint(CalendarResponse &$calendar_response) {
    parent::applyConstraint($calendar_response);

    if ($this->start_date === NULL) {
      $this->start_date = new \DateTime('1970-01-01');
    }
    if ($this->end_date === NULL) {
      $this->end_date = new \DateTime('2999-12-31');
    }

    if ( (($calendar_response->getStartDate()->getTimestamp() >= $this->start_date->getTimestamp() &&
           $calendar_response->getStartDate()->getTimestamp() <= $this->end_date->getTimestamp()) ||
          ($calendar_response->getStartDate()->getTimestamp() <= $this->start_date->getTimestamp() &&
           $calendar_response->getEndDate()->getTimestamp() >= $this->end_date->getTimestamp())) &&
         $this->checkin_day != $calendar_response->getStartDate()->format('N') ) {

      $units = $this->getUnits();

      $included_set = $calendar_response->getIncluded();

      foreach ($included_set as $unit_id => $set) {
        if (isset($units[$unit_id]) || empty($units)) {
          $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::CONSTRAINT, $this);

          $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
        }
      }
    }
  }

  /**
   * Generates a text describing an availability_constraint.
   *
   * @return string
   *   The formatted message.
   */
  public function toString() {
    $text = '';
    $args = array();

    $start_date = FALSE;
    $end_date = FALSE;

    // Day of the week constraint variable.
    $day_of_the_week = $this->getWeekDay($this->checkin_day);

    // Date range constraint variables.
    if ($this->start_date !== NULL && $this->start_date != (new \DateTime('1970-01-01'))) {
      $start_date = $this->start_date->format('Y-m-d');
    }
    if ($this->end_date !== NULL && $this->end_date != (new \DateTime('2999-12-31'))) {
      $end_date = $this->end_date->format('Y-m-d');
    }

    // Finally, build out the constraint text string adding components
    // as necessary.

    // Specify a date range constraint.
    if ($start_date && $end_date) {
      $text = 'From @start_date to @end_date';

      $args['@start_date'] = $start_date;
      $args['@end_date'] = $end_date;
    }

    // Specify the day of the week constraint.
    if ($day_of_the_week) {
      if ($start_date && $end_date) {
        $text = 'From @start_date to @end_date, bookings must start on @day_of_the_week';
      } else {
        $text = 'Bookings must start on @day_of_the_week';
      }

      $args['@day_of_the_week'] = $day_of_the_week;
    }

    return array('text' => $text, 'args' => $args);
  }

  /**
   * @param $day
   * @return string
   */
  private function getWeekDay($day) {
    $weekdays = array(
      1 => 'Monday',
      2 => 'Tuesday',
      3 => 'Wednesday',
      4 => 'Thursday',
      5 => 'Friday',
      6 => 'Saturday',
      7 => 'Sunday',
    );

    return isset($weekdays[$day]) ? $weekdays[$day] : '';
  }

  /**
   * @return int
   */
  public function getCheckinDay() {
    return $this->checkin_day;
  }

  /**
   * @param $checkin_day
   */
  public function setCheckinDay($checkin_day) {
    $this->checkin_day = $checkin_day;
  }

}
