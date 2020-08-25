<?php

/**
 * @file
 * Class Store
 */

namespace Roomify\Bat\Store;

use Roomify\Bat\Event\Event;

/**
 * The basic Store class
 */
abstract class Store implements StoreInterface {

  /**
   * Fill in hourly values from existing events when a day is being split.
   *
   * $existing_events must contain an existing event for the unit the same day.
   * This only needs to be called for hourly granularity.
   *
   * @param array $existing_events
   *   Existing event data from ::getEventData().
   * @param array $itemized
   *   The new event itemized. Values from existing overlapping events will be
   *   inserted into it.
   * @param int $value
   *   The value of the event being added.
   * @param int $unit_id
   *   The unit the event being added.
   * @param int $year
   *   Year of the event.
   * @param int $month
   *   A month of the event.
   * @param int $day
   *   Day of the event.
   */
  protected function itemizeSplitDay(array &$existing_events, array &$itemized, $value, $unit_id, $year, $month, $day) {
    $existing_value = $existing_events[$unit_id][EVENT::BAT_DAY][$year][$month][$day];
    if ($value === -1 && $existing_value > 0) {
      $itemized_day = &$itemized[Event::BAT_HOUR][$year][$month][$day];
      for ($hour = 0; $hour < 24; $hour++) {
        $hour_key = 'h' . $hour;
        $var = &$itemized_day[$hour_key];
        $var = isset($var) && $var != 0 ? $var : $existing_value;
      }
    }
  }

  /**
   * Fill in minute values from existing events when an hour is being split.
   *
   * $existing_events must contain an existing event for the unit during either
   * the same hour or day.
   *
   * @param array $existing_events
   *   Existing event data from ::getEventData().
   * @param array $itemized
   *   The new event itemized. Values from existing overlapping events will be
   *   inserted into it.
   * @param int $value
   *   The value of the event being added.
   * @param int $unit_id
   *   The unit the event being added.
   * @param int $year
   *   Year of the event.
   * @param int $month
   *   A month of the event.
   * @param int $day
   *   A day of the event.
   * @param int $hour
   *   An hour in which an existing event overlaps.
   */
  protected function itemizeSplitHour(array $existing_events, array &$itemized, $value, $unit_id, $year, $month, $day, $hour) {
    if (isset($existing_events[$unit_id][EVENT::BAT_HOUR][$year][$month][$day][$hour])) {
      $existing_value = $existing_events[$unit_id][EVENT::BAT_HOUR][$year][$month][$day][$hour];
    }
    else {
      $existing_value = $existing_events[$unit_id][EVENT::BAT_DAY][$year][$month][$day];
    }
    if ($value === -1 && $existing_value > 0) {
      $itemized_hour = &$itemized[Event::BAT_MINUTE][$year][$month][$day][$hour];
      for ($minute = 0; $minute < 60; $minute++) {
        $minute_key = 'm' . str_pad($minute, 2, '0', STR_PAD_LEFT);
        $var = &$itemized_hour[$minute_key];
        $var = isset($var) && $var != 0 ? $var : $existing_value;
      }
    }
  }

}
