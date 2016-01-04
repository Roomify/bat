<?php

/**
 * @file
 * Class MinMaxConstraint
 */

namespace Roomify\Bat\Constraint;

use Rooimfy\Bat\Constraint;

/**
 *
 */
class MinMaxConstraint extends Constraint {

  /**
   * @var int
   */
  public $min_days = 0;

  /**
   * @var int
   */
  public $max_days = 0;

  /**
   * @var int
   */
  public $checkin_day = NULL;

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
  public function applyConstraint(&$calendar_response) {
    parent::applyConstraint($calendar_response);

    if ($this->start_date->getTimestamp() <= $calendar_response->getStartDate()->getTimestamp() &&
        $this->end_date->getTimestamp() >= $calendar_response->getEndDate()->getTimestamp() &&
        ($this->checkin_day === NULL || $this->checkin_day == $calendar_response->getStartDate()->format('N'))) {

      $units = $this->getUnits();

      $included_set = $calendar_response->getIncluded();

      foreach ($included_set as $unit_id => $set) {
        if (isset($units[$unit_id]) || empty($units)) {
          $start_date = $calendar_response->getStartDate();
          $end_date = $calendar_response->getEndDate();

          $diff = $end_date->diff($start_date)->days;
          if (is_numeric($this->min_days) && $diff < $this->min_days) {
            $calendar_response->removeFromMatched($included_set[$unit_id]['unit'], CalendarResponse::CONSTRAINT, $this);

            $this->affected_units[$unit_id] = $included_set[$unit_id]['unit'];
          }
          elseif (is_numeric($this->max_days) && $diff > $this->max_days) {
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
    $minimum_stay = empty($this->min_days) ? '' : format_plural($this->min_days, '@count day', '@count days', array('@count' => $this->min_days));
    $maximum_stay = empty($this->max_days) ? '' : format_plural($this->max_days, '@count day', '@count days', array('@count' => $this->max_days));

    // Day of the week constraint variable.
    $day_of_the_week = rooms_availability_constraints_get_weekday($this->checkin_day);

    // Date range constraint variables.
    $start_date = $this->start_date->format('Y-m-d');
    $end_date = $this->end_date->format('Y-m-d');

    // Next create replacement placeholders to be used in t() below.
    $args = array(
      '@minimum_stay' => $minimum_stay,
      '@maximum_stay' => $maximum_stay,
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
      if ($start_date && $end_date) {
        $text = t('From @start_date to @end_date, if booking starts on @day_of_the_week', $args);
      }
      else {
        $text = t('If booking starts on @day_of_the_week', $args);
      }
    }

    // Specify the min/max stay length constraint.
    if ($minimum_stay || $maximum_stay) {
      if (empty($text)) {
        $text = t('The stay') . ' ';
      }
      else {
        $text .=   ' ' . t('the stay') . ' ';
      }
    }
    if ($minimum_stay && $maximum_stay) {
      // Special case when min stay and max stay are the same.
      if ($minimum_stay == $maximum_stay) {
        $text .= t('must be for @minimum_stay', $args);
      }
      else {
        $text .= t('must be at least @minimum_stay and at most @maximum_stay', $args);
      }
    }
    elseif ($minimum_stay) {
      $text .= t('must be for at least @minimum_stay', $args);
    }
    elseif ($maximum_stay) {
      $text .= t('cannot be more than @maximum_stay', $args);
    }

    return $text;
  }

}
