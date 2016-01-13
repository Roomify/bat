<?php

/**
 * @file
 * Class Calendar
 */

namespace Roomify\Bat\Calendar;

use Roomify\Bat\Calendar\AbstractCalendar;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit based on BAT's data structure
 */
class Calendar extends AbstractCalendar {

  /**
   * @param $units
   * @param $store
   * @param $default_value
   */
  public function __construct($units, $store, $default_value = 0) {
    $this->units = $units;
    $this->store = $store;
    $this->default_value = $default_value;
  }

}
