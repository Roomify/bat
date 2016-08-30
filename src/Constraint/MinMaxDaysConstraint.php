<?php

/**
 * @file
 * Class MinMaxConstraint
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Calendar\CalendarResponse;
use Roomify\Bat\Constraint\Constraint;

/**
 * Checks that a request is at least a set number of days and does not exceed a
 * set number of days.
 *
 */
class MinMaxDaysConstraint extends Constraint {

  /**
   * @var int
   */
  protected $min_days = 0;

  /**
   * @var int
   */
  protected $max_days = 0;

  /**
   * @var int
   */
  protected $checkin_day = NULL;

  /**
   * @param $min_days
   * @param $max_days
   * @param $start_date
   * @param $end_date
   * @param $checkin_day
   */
  public function __construct($units, $min_days = 0, $max_days = 0, $start_date = NULL, $end_date = NULL, $checkin_day = NULL) {
    parent::__construct($units);

    $this->min_days = $min_days;
    $this->max_days = $max_days;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->checkin_day = $checkin_day;
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
          ($calendar_response->getEndDate()->getTimestamp() >= $this->start_date->getTimestamp() &&
           $calendar_response->getEndDate()->getTimestamp() <= $this->end_date->getTimestamp()) ||
          ($calendar_response->getStartDate()->getTimestamp() <= $this->start_date->getTimestamp() &&
           $calendar_response->getEndDate()->getTimestamp() >= $this->end_date->getTimestamp())) &&
         ($this->checkin_day === NULL || $this->checkin_day == $calendar_response->getStartDate()->format('N')) ) {

      $units = $this->getUnits();

      $included_set = $calendar_response->getIncluded();

      foreach ($included_set as $unit_id => $set) {
        if (isset($units[$unit_id]) || empty($units)) {
          $start_date = $calendar_response->getStartDate();
          $end_date = $calendar_response->getEndDate();

          $temp_end_date = clone($end_date);
          $temp_end_date->add(new \DateInterval('PT1M'));

          $diff = $temp_end_date->diff($start_date)->days;
          if (is_numeric($this->min_days) && $diff < $this->min_days) {
            $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::CONSTRAINT, $this);

            $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
          } elseif (is_numeric($this->max_days) && $diff > $this->max_days) {
            $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::CONSTRAINT, $this);

            $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
          }
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

    // Min/max stay length constraint variables.
    $minimum_stay = empty($this->min_days) ? '' : (($this->min_days == 1) ? $this->min_days . ' day' : $this->min_days . ' days');
    $maximum_stay = empty($this->max_days) ? '' : (($this->max_days == 1) ? $this->max_days . ' day' : $this->max_days . ' days');

    // Day of the week constraint variable.
    $day_of_the_week = $this->getWeekDay($this->checkin_day);

    $start_date = FALSE;
    $end_date = FALSE;

    // Date range constraint variables.
    if ($this->start_date !== NULL && $this->start_date != (new \DateTime('1970-01-01'))) {
      $start_date = $this->start_date->format('Y-m-d');
    }
    if ($this->end_date !== NULL && $this->end_date != (new \DateTime('2999-12-31'))) {
      $end_date = $this->end_date->format('Y-m-d');
    }

    // Next create replacement placeholders to be used in t() below.
    $args = array(
      '@minimum_stay' => $minimum_stay,
      '@maximum_stay' => $maximum_stay,
      '@day_of_the_week' => $day_of_the_week,
    );

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
        $text = 'From @start_date to @end_date, if booking starts on @day_of_the_week';
      } else {
        $text = 'If booking starts on @day_of_the_week';
      }
    }

    // Specify the min/max stay length constraint.
    if ($minimum_stay || $maximum_stay) {
      if (empty($text)) {
        $text = 'The stay ';
      } else {
        $text .= ' the stay ';
      }
    }
    if ($minimum_stay && $maximum_stay) {
      // Special case when min stay and max stay are the same.
      if ($minimum_stay == $maximum_stay) {
        $text .= 'must be for @minimum_stay';
      } else {
        $text .= 'must be at least @minimum_stay and at most @maximum_stay';
      }
    } elseif ($minimum_stay) {
      $text .= 'must be for at least @minimum_stay';
    } elseif ($maximum_stay) {
      $text .= 'cannot be more than @maximum_stay';
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
  public function getMinDays() {
    return $this->min_days;
  }

  /**
   * @param $min_days
   */
  public function setMinDays($min_days) {
    $this->min_days = $min_days;
  }

  /**
   * @return int
   */
  public function getMaxDays() {
    return $this->max_days;
  }

  /**
   * @param $max_days
   */
  public function setMaxDays($max_days) {
    $this->max_days = $max_days;
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
