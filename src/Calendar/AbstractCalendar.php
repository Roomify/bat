<?php

/**
 * @file
 * Class AbstractCalendar
 */

namespace Roomify\Bat\Calendar;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Calendar\CalendarInterface;
use Roomify\Bat\Calendar\CalendarResponse;
use Roomify\Bat\Event\EventItemizer;

/**
 * Handles querying and updating state stores
 */
abstract class AbstractCalendar implements CalendarInterface {

  /**
   * The units we are dealing with. If no unit ids set the calendar will return
   * results for date range and all units within that range.
   *
   * @var array
   */
  protected $units;


  /**
   * The class that will access the actual event store where event data is held.
   *
   * @var
   */
  protected $store;

  /**
   * The default value for events. In the event store this is represented by 0 which is then
   * replaced by the default value provided in the constructor.
   *
   * @var
   */
  protected $default_value;

  /**
   * Stores itemized events allowing us to perform searches over them without having to pull
   * them out of storage (i.e. reducing DB calls)
   *
   * @var array
   */
  protected $itemized_events;

  /**
   * {@inheritdoc}
   */
  public function addEvents($events, $granularity) {

    $added = TRUE;

    foreach ($events as $event) {
      // Events save themselves so here we cycle through each and return true if all events
      // were saved

      $check = $event->saveEvent($this->store, $granularity);

      if ($check == FALSE) {
        $added = FALSE;
        break;
      }
    }

    return $added;
  }

  /**
   * Given a start and end time will retrieve events from the defined store.
   *
   * If unit_ids where defined it will filter for those unit ids.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $reset - if set to TRUE we will always refer to the Store to retrieve events
   *
   * @return array
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date, $reset = TRUE) {
    if ($reset || empty($this->itemized_events)) {
      // We first get events in the itemized format
      $this->itemized_events = $this->getEventsItemized($start_date, $end_date);
    }

    // We then normalize those events to create Events that get added to an array
    return $this->getEventsNormalized($start_date, $end_date, $this->itemized_events);
  }

  /**
   * Given a start and end time this will return the states units find themselves in for that range.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $reset - if set to TRUE we will refer to the Store to retrieve events
   *
   * @return array
   *  An array of states keyed by unit
   */
  public function getStates(\DateTime $start_date, \DateTime $end_date, $reset = TRUE) {
    $events = $this->getEvents($start_date, $end_date, $reset);

    $states = array();
    foreach ($events as $unit => $unit_events) {
      foreach ($unit_events as $event) {
        $states[$unit][$event->getValue()] = $event->getValue();
      }
    }

    return $states;
  }

  /**
   * Given a date range and a set of valid states it will return all units within the
   * set of valid states.
   * If intersect is set to TRUE a unit will report as matched as long as within the time
   * period requested it finds itself at least once within a valid state.
   * Alternatively units will match ONLY if they find themselves withing the valid states and
   * no other state.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $valid_states
   * @param $constraints
   * @param $intersect - performs an intersect rather than a diff on valid states
   * @param $reset - if set to true we refer to the Store to retrieve events
   *
   * @return CalendarResponse
   */
  public function getMatchingUnits(\DateTime $start_date, \DateTime $end_date, $valid_states, $constraints = array(), $intersect = FALSE, $reset = TRUE) {
    $units = array();
    $response = new CalendarResponse($start_date, $end_date, $valid_states);
    $keyed_units = $this->keyUnitsById();

    $states = $this->getStates($start_date, $end_date, $reset);
    foreach ($states as $unit => $unit_states) {
      // Create an array with just the states
      $current_states = array_keys($unit_states);

      // Compare the current states with the set of valid states
      if ($intersect) {
        $remaining_states = array_intersect($current_states, $valid_states);
      }
      else {
        $remaining_states = array_diff($current_states, $valid_states);
      }

      if ((count($remaining_states) == 0 && !$intersect) || (count($remaining_states) > 0 && $intersect)) {
        // Unit is in a state that is within the set of valid states so add to result set
        $units[$unit] = $unit;
        $response->addMatch($keyed_units[$unit], CalendarResponse::VALID_STATE);
      }
      else {
        $response->addMiss($keyed_units[$unit], CalendarResponse::INVALID_STATE);
      }

      $unit_constraints = $keyed_units[$unit]->getConstraints();
      $response->applyConstraints($unit_constraints);
    }

    $response->applyConstraints($constraints);

    return $response;
  }

  /**
   * Provides an itemized array of events keyed by the unit_id and divided by day,
   * hour and minute.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param String $granularity
   *
   * @return array
   */
  public function getEventsItemized(\DateTime $start_date, \DateTime $end_date, $granularity = Event::BAT_HOURLY) {
    // The final events we will return
    $events = array();

    $keyed_units = $this->keyUnitsById();

    $db_events = $this->store->getEventData($start_date, $end_date, array_keys($keyed_units));

    // Create a mock itemized event for the period in question - since event data is either
    // in the database or the default value we first create a mock event and then fill it in
    // accordingly
    $mock_event = new Event($start_date, $end_date, new Unit(0,0), $this->default_value);
    $itemized = $mock_event->itemize(new EventItemizer($mock_event, $granularity));

    // Cycle through each unit retrieved and provide it with a fully configured itemized mock event
    foreach ($db_events as $unit => $event) {
      // Add the mock event
      $events[$unit] = $itemized;

      $events[$unit][Event::BAT_DAY] = $this->itemizeDays($db_events, $itemized, $unit, $keyed_units);

      // Handle hours
      if (isset($itemized[Event::BAT_HOUR]) || isset($db_events[$unit][Event::BAT_HOUR])) {
        $events[$unit][Event::BAT_HOUR] = $this->itemizeHours($db_events, $itemized, $unit, $keyed_units);
      } else {
        // No hours - set an empty array
        $events[$unit][Event::BAT_HOUR] = array();
      }

      // Handle minutes
      if (isset($itemized[Event::BAT_MINUTE]) || isset($db_events[$unit][Event::BAT_MINUTE])) {
        $events[$unit][Event::BAT_MINUTE] = $this->itemizeMinutes($db_events, $itemized, $unit, $keyed_units);
      } else {
        // No minutes - set an empty array
        $events[$unit][Event::BAT_MINUTE] = array();
      }

    }

    // Check to see if any events came back from the db
    foreach ($keyed_units as $id => $unit) {
      // If we don't have any db events add mock events (itemized)
      if ((isset($events[$id]) && count($events[$id]) == 0) || !isset($events[$id])) {
        $empty_event = new Event($start_date, $end_date, $unit, $unit->getDefaultValue());
        $events[$id] = $empty_event->itemize(new EventItemizer($empty_event, $granularity));
      }
    }

    return $events;
  }

  /**
   * Helper function that cycles through db results and setups the BAT_DAY itemized array
   *
   * @param $db_events
   * @param $itemized
   * @param $unit
   * @param $keyed_units
   *
   * @return array
   */
  private function itemizeDays($db_events, $itemized, $unit, $keyed_units) {
    $result = array();

    foreach ($itemized[Event::BAT_DAY] as $year => $months) {
      foreach ($months as $month => $days) {
        // Check if month is defined in DB otherwise set to default value
        if (isset($db_events[$unit][Event::BAT_DAY][$year][$month])) {
          foreach ($days as $day => $value) {
            $result[$year][$month][$day] = ((int)$db_events[$unit][Event::BAT_DAY][$year][$month][$day] == 0 ? $keyed_units[$unit]->getDefaultValue() : (int)$db_events[$unit][Event::BAT_DAY][$year][$month][$day]);
          }
        }
        else {
          foreach ($days as $day => $value) {
            $result[$year][$month][$day] = $keyed_units[$unit]->getDefaultValue();
          }
        }
      }
    }

    return $result;
  }

  /**
   * Helper function that cycles through db results and setups the BAT_HOUR itemized array
   * @param $db_events
   * @param $itemized
   * @param $unit
   * @param $keyed_units
   *
   * @return array
   */
  private function itemizeHours($db_events, $itemized, $unit, $keyed_units) {

    $result = array();

    if (isset($itemized[Event::BAT_HOUR])) {
      foreach ($itemized[Event::BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              if (isset($db_events[$unit][Event::BAT_HOUR][$year][$month][$day][$hour])) {
                $result[$year][$month][$day][$hour] = ((int) $db_events[$unit][Event::BAT_HOUR][$year][$month][$day][$hour] == 0 ? $keyed_units[$unit]->getDefaultValue() : (int) $db_events[$unit][Event::BAT_HOUR][$year][$month][$day][$hour]);
              }
              else {
                // If nothing from db - then revert to the defaults
                $result[$year][$month][$day][$hour] = (int) $keyed_units[$unit]->getDefaultValue();
              }
            }
          }
        }
      }
    }

    // Now fill in hour data coming from the database which the mock event did *not* cater for in the data structure
    if (isset($db_events[$unit][Event::BAT_HOUR])) {
      foreach ($db_events[$unit][Event::BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              $result[$year][$month][$day][$hour] = ((int) $value == 0 ? $keyed_units[$unit]->getDefaultValue() : (int) $value);
            }
            ksort($result[$year][$month][$day], SORT_NATURAL);
          }
        }
      }
    }

    return $result;
  }

  /**
   * Helper function that cycles through db results and setups the BAT_MINUTE itemized array
   *
   * @param $db_events
   * @param $itemized
   * @param $unit
   * @param $keyed_units
   *
   * @return array
   */
  private function itemizeMinutes($db_events, $itemized, $unit, $keyed_units) {
    $result = array();

    if (isset($itemized[Event::BAT_MINUTE])) {
      foreach ($itemized[Event::BAT_MINUTE] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                if (isset($db_events[$unit][Event::BAT_MINUTE][$year][$month][$day][$hour][$minute])) {
                  $result[$year][$month][$day][$hour][$minute] = ((int) $db_events[$unit][Event::BAT_MINUTE][$year][$month][$day][$hour][$minute] == 0 ? $keyed_units[$unit]->getDefaultValue() : (int) $db_events[$unit][Event::BAT_MINUTE][$year][$month][$day][$hour][$minute]);
                }
                else {
                  // If nothing from db - then revert to the defaults
                  $result[$year][$month][$day][$hour][$minute] = (int) $keyed_units[$unit]->getDefaultValue();
                }
              }
            }
          }
        }
      }
    }

    // Now fill in minute data coming from the database which the mock event did *not* cater for
    if (isset($db_events[$unit][Event::BAT_MINUTE])) {
      foreach ($db_events[$unit][Event::BAT_MINUTE] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                $result[$year][$month][$day][$hour][$minute] = ((int) $value == 0 ? $keyed_units[$unit]->getDefaultValue() : (int) $value);
              }
              ksort($result[$year][$month][$day][$hour], SORT_NATURAL);
            }
          }
        }
      }
    }

    return $result;
  }

  /**
   * Given an itemized set of event data it will return an array of Events
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $events
   *
   * @return array
   */
  public function getEventsNormalized(\DateTime $start_date, \DateTime $end_date, $events) {
    // Daylight Saving Time
    $timezone = new \DateTimeZone(date_default_timezone_get());
    $transitions = $timezone->getTransitions($start_date->getTimestamp(), $end_date->getTimestamp());

    $dst_transitions = array();
    unset($transitions[0]);
    foreach ($transitions as $transition) {
      if ($transition['isdst']) {
        $dst_transitions[] = $transition['ts'] - 60;
      }
    }
    $is_daylight_saving_time = (empty($dst_transitions)) ? FALSE : TRUE;

    $normalized_events = array();

    $events_copy = $events;

    foreach ($events_copy as $unit_id => $data) {

      // Make sure years are sorted
      ksort($data[Event::BAT_DAY]);
      if (isset($data[Event::BAT_HOUR])) ksort($data[Event::BAT_HOUR]);
      if (isset($data[Event::BAT_MINUTE])) ksort($data[Event::BAT_MINUTE]);

      // Set up variables to keep track of stuff
      $current_value = NULL;
      $start_event = new \DateTime();
      $end_event = new \DateTime();

      foreach ($data[Event::BAT_DAY] as $year => $months) {
        // Make sure months are in right order
        ksort($months);
        foreach ($months as $month => $days) {
          foreach ($days as $day => $value) {
            if ($value == -1) {
              // Retrieve hour data
              $hour_data = $events[$unit_id][Event::BAT_HOUR][$year][$month][$day];
              ksort($hour_data, SORT_NATURAL);
              foreach ($hour_data as $hour => $hour_value) {
                if ($hour_value == -1) {
                  // We are going to need minute values
                  $minute_data = $events[$unit_id][Event::BAT_MINUTE][$year][$month][$day][$hour];
                  ksort($minute_data, SORT_NATURAL);
                  foreach ($minute_data as $minute => $minute_value) {
                    if ($current_value === $minute_value) {
                      // We are still in minutes and going through so add a minute
                      $end_event->add(new \DateInterval('PT1M'));
                    }
                    elseif (($current_value != $minute_value) && ($current_value !== NULL)) {
                      // Value just switched - let us wrap up with current event and start a new one
                      $normalized_events[$unit_id][] = new Event($start_event, $end_event, $this->getUnit($unit_id), $current_value);
                      $start_event = clone($end_event->add(new \DateInterval('PT1M')));
                      $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':' . substr($minute,1));
                      $current_value = $minute_value;
                    }
                    if ($current_value === NULL) {
                      // We are down to minutes and haven't created and event yet - do one now
                      $start_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':' . substr($minute,1));
                      $end_event = clone($start_event);
                    }
                    $current_value = $minute_value;
                  }
                }
                elseif ($current_value === $hour_value) {
                  // We are in hours and can add something
                  $end_event->add(new \DateInterval('PT1H'));
                }
                elseif (($current_value != $hour_value) && ($current_value !== NULL)) {
                  $skip_finalize_event = FALSE;

                  if ($is_daylight_saving_time) {
                    if (in_array($end_event->getTimestamp(), $dst_transitions)) {
                      $skip_finalize_event = TRUE;
                    }
                  }

                  if ($skip_finalize_event === FALSE) {
                    // Value just switched - let us wrap up with current event and start a new one
                    $normalized_events[$unit_id][] = new Event($start_event, $end_event, $this->getUnit($unit_id), $current_value);
                    // Start event becomes the end event with a minute added
                    $start_event = clone($end_event->add(new \DateInterval('PT1M')));
                    // End event comes the current point in time
                    $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':59');
                    $current_value = $hour_value;
                  }
                }
                if ($current_value === NULL) {
                  // Got into hours and still haven't created an event so
                  $start_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':00');
                  // We will be occupying at least this hour so might as well mark it
                  $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':59');
                  $current_value = $hour_value;
                }
              }
            }
            elseif ($current_value === $value) {
              // We are adding a whole day so the end event gets moved to the end of the day we are adding
              $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '23:59');
            }
            elseif (($current_value !== $value) && ($current_value !== NULL)) {
              // Value just switched - let us wrap up with current event and start a new one
              $normalized_events[$unit_id][] = new Event($start_event, $end_event, $this->getUnit($unit_id), $current_value);
              // Start event becomes the end event with a minute added
              $start_event = clone($end_event->add(new \DateInterval('PT1M')));
              // End event becomes the current day which we have not account for yet
              $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '23:59');
              $current_value = $value;
            }
            if ($current_value === NULL) {
              // We have not created an event yet so let's do it now
              $start_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '00:00');
              $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . '23:59');
              $current_value = $value;
            }
          }
        }
      }

      // Add the last event in for which there is nothing in the loop to catch it
      $normalized_events[$unit_id][] = new Event($start_event, $end_event, $this->getUnit($unit_id), $current_value);
    }

    // Given the database structure we may get events that are not with the date ranges we were looking for
    // We get rid of them here so that the user has a clean result.
    foreach ($normalized_events as $unit_id => $events) {
      foreach ($events as $key => $event) {
        if ($event->overlaps($start_date, $end_date)) {
          // Adjust start or end dates of events so everything is within range
          if ($event->startsEarlier($start_date)) {
            $event->setStartDate($start_date);
          }
          if ($event->endsLater($end_date)) {
            $event->setEndDate($end_date);
          }
        }
        else {
          // Event completely not in range so unset it
          unset($normalized_events[$unit_id][$key]);
        }
      }
    }

    return $normalized_events;
  }

  /**
   * A simple utility function that given an array of datum=>value will group results based on
   * those that have the same value. Useful for grouping events based on state.
   *
   * @param $data
   * @param $length
   */
  public function groupData($data, $length) {
    $flipped = array();
    $e = 0;
    $j = 0;
    $old_value = NULL;

    foreach ($data as $datum => $value) {
      $j++;
      if ($j <= $length) {
        // If the value has changed and we are not just starting
        if (($value != $old_value)) {
          $e++;
          $flipped[$e][$value][$datum] = $datum;
          $old_value = $value;
        }
        else {
          $flipped[$e][$value][$datum] = $datum;
        }
      }
    }
  }

  /**
   * Return an array of unit ids from the set of units
   * supplied to the Calendar.
   *
   * @return array
   */
  protected function getUnitIds() {
    $unit_ids = array();
    foreach ($this->units as $unit) {
      $unit_ids[] = $unit->getUnitId();
    }

    return $unit_ids;
  }

  /**
   * Return an array of units keyed by unit id
   *
   * @return array
   */
  protected function keyUnitsById() {
    $keyed_units = array();
    foreach ($this->units as $unit) {
      $keyed_units[$unit->getUnitId()] = $unit;
    }

    return $keyed_units;
  }

  /**
   * Returns the unit object.
   *
   * @param $unit_id
   * @return Unit
   */
  protected function getUnit($unit_id) {
    $keyed =  $this->keyUnitsById();
    return $keyed[$unit_id];
  }

}
