<?php

/**
 * @file
 * Class DrupalDBStore
 */

namespace Roomify\Bat\Store;

use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventItemizer;
use Roomify\Bat\Store\SqlDBStore;

/**
 * This is a Drupal-specific implementation of the Store.
 *
 */
class DrupalDBStore extends SqlDBStore {

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
      if (class_exists('Drupal') && floatval(\Drupal::VERSION) >= 9) {
        $results[$type] = \Drupal\Core\Database\Database::getConnection()->query($query);
      } else {
        $results[$type] = db_query($query);
      }
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
        $db_events[$data['unit_id']][Event::BAT_HOUR][$data['year']][$data['month']]['d' . $data['day']]['h'. $i] = $data['h'.$i];
      }
    }

    // With the hour events taken care off let's cycle through minutes
    while( $data = $results[Event::BAT_MINUTE]->fetchAssoc()) {
      for ($i = 0; $i<=59; $i++) {
        if ($i <= 9) {
          $index = 'm0'.$i;
        } else {
          $index = 'm'.$i;
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
    if (class_exists('Drupal') && floatval(\Drupal::VERSION) >= 9) {
      $transaction = \Drupal\Core\Database\Database::getConnection()->startTransaction();
    } else {
      $transaction = db_transaction();
    }

    // Get existing event data from db
    $existing_events = $this->getEventData($event->getStartDate(), $event->getEndDate(), array($event->getUnitId()));

    try {
      // Itemize an event so we can save it
      $itemized = $event->itemize(new EventItemizer($event, $granularity));

      //Write days
      foreach ($itemized[Event::BAT_DAY] as $year => $months) {
        foreach ($months as $month => $days) {
          if ($granularity === Event::BAT_HOURLY) {
            foreach ($days as $day => $value) {
              $this->itemizeSplitDay($existing_events, $itemized, $value, $event->getUnitId(), $year, $month, $day);
            }
          }
          if (class_exists('Drupal') && floatval(\Drupal::VERSION) >= 9) {
            \Drupal\Core\Database\Database::getConnection()->merge($this->day_table_no_prefix)
              ->key(array(
                'unit_id' => $event->getUnitId(),
                'year' => $year,
                'month' => $month
              ))
              ->fields($days)
              ->execute();
          } else {
            db_merge($this->day_table_no_prefix)
              ->key(array(
                'unit_id' => $event->getUnitId(),
                'year' => $year,
                'month' => $month
              ))
              ->fields($days)
              ->execute();
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
                foreach ($hours as $hour => $value){
                  $this->itemizeSplitHour($existing_events, $itemized, $value, $event->getUnitId(), $year, $month, $day, $hour);
                }
                if (class_exists('Drupal') && floatval(\Drupal::VERSION) >= 9) {
                  \Drupal\Core\Database\Database::getConnection()->merge($this->hour_table_no_prefix)
                    ->key(array(
                      'unit_id' => $event->getUnitId(),
                      'year' => $year,
                      'month' => $month,
                      'day' => substr($day, 1)
                    ))
                    ->fields($hours)
                    ->execute();
                } else {
                  db_merge($this->hour_table_no_prefix)
                    ->key(array(
                      'unit_id' => $event->getUnitId(),
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
        }

        // If we have minutes write minutes
        foreach ($itemized[Event::BAT_MINUTE] as $year => $months) {
          foreach ($months as $month => $days) {
            foreach ($days as $day => $hours) {
              foreach ($hours as $hour => $minutes) {
                if (class_exists('Drupal') && floatval(\Drupal::VERSION) >= 9) {
                  \Drupal\Core\Database\Database::getConnection()->merge($this->minute_table_no_prefix)
                    ->key(array(
                      'unit_id' => $event->getUnitId(),
                      'year' => $year,
                      'month' => $month,
                      'day' => substr($day, 1),
                      'hour' => substr($hour, 1)
                    ))
                    ->fields($minutes)
                    ->execute();
                } else {
                  db_merge($this->minute_table_no_prefix)
                    ->key(array(
                      'unit_id' => $event->getUnitId(),
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
    } catch (\Exception $e) {
      $stored = FALSE;
      $transaction->rollback();
      watchdog_exception('BAT Event Save Exception', $e);
    }

    return $stored;
  }

}
