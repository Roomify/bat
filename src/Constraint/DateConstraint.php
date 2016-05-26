<?php

/**
 * @file
 * Class DateConstraint
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Calendar\CalendarResponse;
use Roomify\Bat\Constraint\Constraint;

/**
 * The DateConstraint provides a generalized constraint on the start and end time
 * of an event.
 *
 * An applicable scenario is to allow units to declare (independently of
 * their actual event state) whether they should be deemed as matching a search
 * based on the dates of the search. For example, a hotel room can declare that it
 * should be unavailable if the check-in time is not at least 2 days away from the
 * today.
 */
class DateConstraint extends Constraint {

  // The constraint start date - if on or after requested start date constraint will apply.
  public $start_date;

  // The constraint end date - if on or after requested end date constraint will apply.
  public $end_date;

  /**
   * DateConstraint constructor.
   * @param array $units
   * @param null $start_date
   * @param null $end_date
   */
  public function __construct($units, $start_date = NULL, $end_date = NULL) {
    parent::__construct($units);

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

    // If the constraint start date is on or after the requested start date
    // or the constraint end date is on or before the requested end date, mark
    // the units as invalid.
    if ($this->start_date->getTimestamp() >= $calendar_response->getStartDate()->getTimestamp() ||
        $this->end_date->getTimestamp() <= $calendar_response->getEndDate()->getTimestamp()) {

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

    // Date range constraint variables.
    if ($this->start_date !== NULL) {
      $start_date = $this->start_date->format('Y-m-d');
    }
    if ($this->start_date !== NULL) {
      $end_date = $this->end_date->format('Y-m-d');
    }

    // Specify a date range constraint.
    if ($start_date && $end_date) {
      $text = 'From @start_date to @end_date';

      $args['@start_date'] = $start_date;
      $args['@end_date'] = $end_date;
    }

    // Specify the start/end constraint.
    if ($start_date && $end_date) {
      $text = 'From @start_date to @end_date';
    }

    return array('text' => $text, 'args' => $args);
  }

}
