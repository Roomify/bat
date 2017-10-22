<?php

/**
 * @file
 * Class SqlDBStore
 */

namespace Roomify\Bat\Store;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventItemizer;
use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Store\SqlDBStore;

/**
 * This is a generic Sql implementation of the Store.
 *
 */
abstract class SqlDBStore extends Store {

  // There are two types of stores - for event ids and status
  const BAT_EVENT = 'event';
  const BAT_STATE = 'state';

  /**
   * The table that holds day data.
   * @var
   */
  public $day_table;

  /**
   * The table that holds hour data.
   * @var
   */
  public $hour_table;

  /**
   * The table that holds minute data.
   * @var
   */
  public $minute_table;

  /**
   * The table that holds day data without prefix.
   * @var
   */
  public $day_table_no_prefix;

  /**
   * The table that holds hour data without prefix.
   * @var
   */
  public $hour_table_no_prefix;

  /**
   * The table that holds minute data without prefix.
   * @var
   */
  public $minute_table_no_prefix;

  /**
   * The event type we are dealing with.
   * @var
   */
  public $event_type;

  /**
   * SqlDBStore constructor.
   *
   * Provided with the event type it will determine the appropriate table names to
   * store data in. This assumes standard behaviour from Bat_Event
   * @param $event_type
   * @param string $event_data
   */
  public function __construct($event_type, $event_data = 'state', $prefix = '') {

    $this->event_type = $event_type;

    if ($event_data == SqlDBStore::BAT_STATE) {
      $this->day_table = $prefix . 'bat_event_' . $event_type . '_day_' . SqlDBStore::BAT_STATE;
      $this->hour_table = $prefix . 'bat_event_' . $event_type . '_hour_' . SqlDBStore::BAT_STATE;
      $this->minute_table = $prefix . 'bat_event_' . $event_type . '_minute_' . SqlDBStore::BAT_STATE;

      $this->day_table_no_prefix = 'bat_event_' . $event_type . '_day_' . SqlDBStore::BAT_STATE;
      $this->hour_table_no_prefix = 'bat_event_' . $event_type . '_hour_' . SqlDBStore::BAT_STATE;
      $this->minute_table_no_prefix = 'bat_event_' . $event_type . '_minute_' . SqlDBStore::BAT_STATE;
    }

    if ($event_data == SqlDBStore::BAT_EVENT) {
      $this->day_table = $prefix . 'bat_event_' . $event_type . '_day_' . SqlDBStore::BAT_EVENT;
      $this->hour_table = $prefix . 'bat_event_' . $event_type . '_hour_' . SqlDBStore::BAT_EVENT;
      $this->minute_table = $prefix . 'bat_event_' . $event_type . '_minute_' . SqlDBStore::BAT_EVENT;

      $this->day_table_no_prefix = 'bat_event_' . $event_type . '_day_' . SqlDBStore::BAT_EVENT;
      $this->hour_table_no_prefix = 'bat_event_' . $event_type . '_hour_' . SqlDBStore::BAT_EVENT;
      $this->minute_table_no_prefix = 'bat_event_' . $event_type . '_minute_' . SqlDBStore::BAT_EVENT;
    }

  }

  /**
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $unit_ids
   *
   * @return array
   */
  public function buildQueries(\DateTime $start_date, \DateTime $end_date, $unit_ids) {
    $queries = array();

    $queries[Event::BAT_DAY] = 'SELECT * FROM ' . $this->day_table . ' WHERE ';
    $queries[Event::BAT_HOUR] = 'SELECT * FROM ' . $this->hour_table . ' WHERE ';
    $queries[Event::BAT_MINUTE] = 'SELECT * FROM ' . $this->minute_table . ' WHERE ';

    // Create a mock event which we will use to determine how to query the database
    $mock_event = new Event($start_date, $end_date, new Unit(0, 0, array()), -10);
    // We don't need a granular event even if we are retrieving granular data - since we don't
    // know what the event break-down is going to be we need to get the full range of data from
    // days, hours and minutes.
    $itemized = $mock_event->itemize(new EventItemizer($mock_event, Event::BAT_DAILY));

    $year_count = 0;

    $query_parameters = '';

    foreach ($itemized[Event::BAT_DAY] as $year => $months) {
      if ($year_count > 0) {
        // We are dealing with multiple years so add an OR
        $query_parameters .= ' OR ';
      }
      $query_parameters .= 'year IN (' . $year . ') ';
      $query_parameters .= 'AND month IN (' . implode(",", array_keys($months)) . ') ';
      if (count($unit_ids) > 0) {
        // Unit ids are defined so add this as a filter
        $query_parameters .= 'AND unit_id in (' . implode("," , $unit_ids) . ') ';
      }
      $year_count++;
    }

    // Add parameters to each query
    $queries[Event::BAT_DAY] .= $query_parameters;
    $queries[Event::BAT_HOUR] .= $query_parameters;
    $queries[Event::BAT_MINUTE] .= $query_parameters;

    // Clean up and add ordering information
    $queries[Event::BAT_DAY] .= ' ORDER BY unit_id, year, month';
    $queries[Event::BAT_HOUR] .= ' ORDER BY unit_id, year, month, day';
    $queries[Event::BAT_MINUTE] .= ' ORDER BY unit_id, year, month, day, hour';

    return $queries;
  }

}
