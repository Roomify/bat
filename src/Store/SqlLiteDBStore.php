<?php

/**
 * @file
 * Class SqlLiteDBStore
 */

namespace Roomify\Bat\Store;

use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventItemizer;
use Roomify\Bat\Store\SqlDBStore;

/**
 * This is a SqlLite implementation of the Store.
 *
 */
class SqlLiteDBStore extends SqlDBStore {

  protected $pdo;

  public function __construct(\PDO $pdo, $event_type, $event_data = 'state') {
    parent::__construct($event_type, $event_data);

    $this->pdo = $pdo;
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

    $queries = $this->buildQueries($start_date, $end_date, $unit_ids);

    $results = array();
    // Run each query and store results
    foreach ($queries as $type => $query) {
      $results[$type] = $this->pdo->query($query);
    }

    $db_events = array();

    // Cycle through day results and setup an event array
    foreach ($results[Event::BAT_DAY]->fetchAll() as $data) {
      // Figure out how many days the current month has
      $temp_date = new \DateTime($data['year'] . "-" . $data['month']);
      $days_in_month = (int)$temp_date->format('t');
      for ($i = 1; $i <= $days_in_month; $i++) {
        $db_events[$data['unit_id']][Event::BAT_DAY][$data['year']][$data['month']]['d' . $i] = $data['d' . $i];
      }
    }

    // With the day events taken care off let's cycle through hours
    foreach ($results[Event::BAT_HOUR]->fetchAll() as $data) {
      for ($i = 0; $i <= 23; $i++) {
        $db_events[$data['unit_id']][Event::BAT_HOUR][$data['year']][$data['month']]['d' . $data['day']]['h' . $i] = $data['h' . $i];
      }
    }

    // With the hour events taken care off let's cycle through minutes
    foreach ($results[Event::BAT_MINUTE]->fetchAll() as $data) {
      for ($i = 0; $i <= 59; $i++) {
        if ($i <= 9) {
          $index = 'm0' . $i;
        } else {
          $index = 'm' . $i;
        }
        $db_events[$data['unit_id']][Event::BAT_MINUTE][$data['year']][$data['month']]['d' . $data['day']]['h' . $data['hour']][$index] = $data[$index];
      }
    }

    return $db_events;
  }

  /**
   * @param \Roomify\Bat\Event\EventInterface $event
   * @param $granularity
   *
   * @return bool
   */
  public function storeEvent(EventInterface $event, $granularity = Event::BAT_HOURLY) {
    $stored = TRUE;

    // Get existing event data from db
    $existing_events = $this->getEventData($event->getStartDate(), $event->getEndDate(), array($event->getUnitId()));

    try {
      // Itemize an event so we can save it
      $itemized = $event->itemize(new EventItemizer($event, $granularity));

      // Write days
      foreach ($itemized[Event::BAT_DAY] as $year => $months) {
        foreach ($months as $month => $days) {
          $values = array_values($days);
          $keys = array_keys($days);
          // Because SQLite does not have a nice merge first we have to check if a row exists to determine whether to do an insert or an update
          if (isset($existing_events[$event->getUnitId()][EVENT::BAT_DAY][$year][$month])) {
            $command = "UPDATE $this->day_table SET ";
            foreach ($days as $day => $value) {
              $command .= "$day = $value,";
            }
            $command = rtrim($command, ',');
            $command .= " WHERE unit_id = " . $event->getUnitId() . " AND year = $year AND month = $month";
            $this->pdo->exec($command);
          }
          else {
            $this->pdo->exec("INSERT INTO $this->day_table (unit_id, year, month, " . implode(', ', $keys) . ") VALUES (" . $event->getUnitId() . ", $year, $month, " . implode(', ', $values) . ")");
          }
        }
      }

      if (($granularity == Event::BAT_HOURLY) && isset($itemized[Event::BAT_HOUR])) {
        // Write Hours
        foreach ($itemized[Event::BAT_HOUR] as $year => $months) {
          foreach ($months as $month => $days) {
            foreach ($days as $day => $hours) {
              // Count required as we may receive empty hours for granular events that start and end on midnight
              if (count($hours) > 0) {
                $values = array_values($hours);
                $keys = array_keys($hours);
                if (isset($existing_events[$event->getUnitId()][EVENT::BAT_HOUR][$year][$month][$day])) {
                  $command = "UPDATE $this->hour_table SET ";
                  foreach ($hours as $hour => $value){
                    $command .= "$hour = $value,";
                  }
                  $command = rtrim($command, ',');
                  $command .= " WHERE unit_id = " . $event->getUnitId() . " AND year = $year AND month = $month AND day = " . substr($day,1);
                  $this->pdo->exec($command);
                } else {
                  $this->pdo->exec("INSERT INTO $this->hour_table (unit_id, year, month, day, " . implode(', ', $keys) . ") VALUES (" . $event->getUnitId() . ", $year, $month, " . substr($day, 1) . ", " . implode(', ', $values) . ")");
                }
              }
            }
          }
        }

        // If we have minutes write minutes
        foreach ($itemized[Event::BAT_MINUTE] as $year => $months) {
          foreach ($months as $month => $days) {
            foreach ($days as $day => $hours) {
              foreach ($hours as $hour => $minutes) {
                $values = array_values($minutes);
                $keys = array_keys($minutes);
                if (isset($existing_events[$event->getUnitId()][EVENT::BAT_MINUTE][$year][$month][$day][$hour])) {
                  $command = "UPDATE $this->minute_table SET ";
                  foreach ($minutes as $minute => $value){
                    $command .= "$minute = $value,";
                  }
                  $command = rtrim($command, ',');
                  $command .= " WHERE unit_id = " . $event->getUnitId() . " AND year = $year AND month = $month AND day = " . substr($day,1) . " AND hour = " . substr($hour,1);
                  $this->pdo->exec($command);
                } else {
                  $this->pdo->exec("INSERT INTO $this->minute_table (unit_id, year, month, day, hour, " . implode(', ', $keys) . ") VALUES (" . $event->getUnitId() . ", $year, $month, " . substr($day, 1) . ", " . substr($hour, 1) . ", " . implode(', ', $values) . ")");
                }
              }
            }
          }
        }
      }
    } catch (\Exception $e) {
      $stored = FALSE;
    }

    return $stored;
  }

}
