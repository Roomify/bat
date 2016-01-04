<?php

/**
 * @file
 * Class DrupalDBStore
 */

namespace Drupal\bat;

/**
 * This is a Drupal-specific implementation of the Store.
 *
 */
class DrupalDBStore extends Store {

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
   * The event type we are dealing with.
   * @var
   */
  public $event_type;


  /**
   * DrupalDBStore constructor.
   *
   * Provided with the event type it will determine the appropriate table names to
   * store data in. This assumes standard behaviour from Bat_Event
   * @param $event_type
   * @param string $event_data
   */
  public function __construct($event_type, $event_data = 'state') {

    $this->event_type = $event_type;

    if ($event_data == DrupalDBStore::BAT_STATE) {
      $this->day_table = 'bat_event_' . $event_type . '_day_' . DrupalDBStore::BAT_STATE;
      $this->hour_table = 'bat_event_' . $event_type . '_hour_' . DrupalDBStore::BAT_STATE;
      $this->minute_table = 'bat_event_' . $event_type . '_minute_' . DrupalDBStore::BAT_STATE;
    }

    if ($event_data == DrupalDBStore::BAT_EVENT) {
      $this->day_table = 'bat_event_' . $event_type . '_day_' . DrupalDBStore::BAT_EVENT;
      $this->hour_table = 'bat_event_' . $event_type . '_hour_' . DrupalDBStore::BAT_EVENT;
      $this->minute_table = 'bat_event_' . $event_type . '_minute_' . DrupalDBStore::BAT_EVENT;
    }

  }

  /**
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $unit_ids
   *
   * @return array
   */
  public function getEventData(\DateTime $start_date, \DateTime $end_date, $unit_ids) {

    $queries  = $this->buildQueries($start_date, $end_date, $unit_ids);

    $results = array();
    // Run each query and store results
    foreach ($queries as $type => $query) {
      $results[$type] = db_query($query);
    }

    $db_events = array();

    // Cycle through day results and setup an event array
    while( $data = $results[Event::BAT_DAY]->fetchAssoc()) {
      // Figure out how many days the current month has
      $temp_date = new \DateTime($data['year'] . "-" . $data['month']);
      $days_in_month = (int)$temp_date->format('t');
      for ($i = 1; $i<=$days_in_month; $i++) {
        $db_events[$data['unit_id']][Event::BAT_DAY][$data['year']][$data['month']]['d' . $i] = $data['d'.$i];
      }
    }

    // With the day events taken care off let's cycle through hours
    while( $data = $results[Event::BAT_HOUR]->fetchAssoc()) {
      for ($i = 0; $i<=23; $i++) {
        $db_events[$data['unit_id']][Event::BAT_HOUR][$data['year']][$data['month']][$data['day']]['h'. $i] = $data['h'.$i];
      }
    }

    // With the hour events taken care off let's cycle through minutes
    while( $data = $results[Event::BAT_MINUTE]->fetchAssoc()) {
      for ($i = 0; $i<=59; $i++) {
        if ($i <= 9) {
          $index = 'm0'.$i;
        }
        else {
          $index = 'm'.$i;
        }
        $db_events[$data['unit_id']][Event::BAT_MINUTE][$data['year']][$data['month']][$data['day']][$data['hour']][$index] = $data[$index];
      }
    }

    return $db_events;
  }

  /**
   * @param \Roomify\Bat\Event\Event $event
   * @param $granularity
   *
   * @return bool
   */
  public function storeEvent(Event $event, $granularity = BAT_HOURLY) {
    $stored = TRUE;
    $transaction = db_transaction();

    try {
      // Itemize an event so we can save it
      $itemized = $event->itemizeEvent($granularity);

      //Write days
      foreach ($itemized[BAT_DAY] as $year => $months) {
        foreach ($months as $month => $days) {
          db_merge($this->day_table)
            ->key(array(
              'unit_id' => $event->unit_id,
              'year' => $year,
              'month' => $month
            ))
            ->fields($days)
            ->execute();
        }
      }

      if ($granularity == BAT_HOURLY) {
        // Write Hours
        foreach ($itemized[BAT_HOUR] as $year => $months) {
          foreach ($months as $month => $days) {
            foreach ($days as $day => $hours) {
              // Count required as we may receive empty hours for granular events that start and end on midnight
              if (count($hours) > 0) {
                db_merge($this->hour_table)
                  ->key(array(
                    'unit_id' => $event->unit_id,
                    'year' => $year,
                    'month' => $month,
                    'day' => substr($day, 1)
                  ))
                  ->fields($hours)
                  ->execute();
              }
            }
          }
        }

        //If we have minutes write minutes
        foreach ($itemized[BAT_MINUTE] as $year => $months) {
          foreach ($months as $month => $days) {
            foreach ($days as $day => $hours) {
              foreach ($hours as $hour => $minutes) {
                db_merge($this->minute_table)
                  ->key(array(
                    'unit_id' => $event->unit_id,
                    'year' => $year,
                    'month' => $month,
                    'day' => substr($day, 1),
                    'hour' => substr($hour, 1)
                  ))
                  ->fields($minutes)
                  ->execute();
              }
            }
          }
        }
      }
    }
    catch (\Exception $e) {
      $saved = FALSE;
      $transaction->rollback();
      watchdog_exception('BAT Event Save Exception', $e);
    }

    return $stored;
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

    $hours_query = TRUE;
    $minutes_query = TRUE;

    // Create a mock event which we will use to determine how to query the database
    $mock_event = new Event($start_date, $end_date, 0, -10);
    // We don't need a granular event even if we are retrieving granular data - since we don't
    // know what the event break-down is going to be we need to get the full range of data from
    // days, hours and minutes.
    $itemized = $mock_event->itemizeEvent(BAT_DAILY);

    $year_count = 0;
    $hour_count = 0;
    $minute_count = 0;

    $query_parameters = '';

    foreach($itemized[BAT_DAY] as $year => $months) {
      if ($year_count > 0) {
        // We are dealing with multiple years so add an OR
        $query_parameters .= ' OR ';
      }
      $query_parameters .= 'year IN (' . $year . ') ';
      $query_parameters .= 'AND month IN (' . implode("," ,array_keys($months)) .') ';
      if (count($unit_ids) > 0) {
        // Unit ids are defined so add this as a filter
        $query_parameters .= 'AND unit_id in (' . implode("," , $unit_ids) .') ';
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
