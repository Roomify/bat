<?php

/**
 * @file
 * Class CheckInDayConstraint
 */

namespace Roomify\Bat\Constraint;

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
  public function __construct($units, $checkin_day, $start_date, $end_date) {
    parent::__construct($units);

    $this->checkin_day = $checkin_day;
    $this->start_date = $start_date;
    $this->end_date = $end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function applyConstraint(&$calendar_response) {
    parent::applyConstraint($calendar_response);

    if ($this->start_date->getTimestamp() <= $calendar_response->getStartDate()->getTimestamp() &&
        $this->end_date->getTimestamp() >= $calendar_response->getEndDate()->getTimestamp() &&
        $this->checkin_day !== $calendar_response->getStartDate()->format('N')) {

      $units = $this->getUnits();

      $included_set = $calendar_response->getIncluded();

      foreach ($included_set as $unit_id => $set) {
        if (isset($units[$unit_id]) || empty($units)) {
          $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::INVALID_STATE);

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

    // Day of the week constraint variable.
    $day_of_the_week = rooms_availability_constraints_get_weekday($this->checkin_day);

    // Date range constraint variables.
    // @todo: format date string.
    $start_date = $this->start_date->format('Y-m-d');
    $end_date = $this->end_date->format('Y-m-d');

    // Next create replacement placeholders to be used in t() below.
    $args = array(
      '@start_date' => $start_date,
      '@end_date' => $end_date,
      '@day_of_the_week' => $day_of_the_week,
    );

    // Finally, build out the constraint text string adding components
    // as necessary.

    // Specify a date range constraint.
    if ($start_date && $end_date) {
      $text = t('From @start_date to @end_date', $args);
    }

    // Specify the day of the week constraint.
    if ($day_of_the_week) {
      $text = t('From @start_date to @end_date, bookings must start on @day_of_the_week', $args);
    }

    return $text;
  }

}
