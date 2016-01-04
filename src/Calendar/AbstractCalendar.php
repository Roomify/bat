<?php

/**
 * @file
 * Class AbstractCalendar
 */

namespace Roomify\Bat\Calendar\AbstractCalendar;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\CalendarInterface;
use Roomify\Bat\Calendar\CalendarResponse;

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
  public $units;


  /**
   * The class that will access the actual event store where event data is held.
   *
   * @var
   */
  public $store;

  /**
   * The default value for events. In the event store this is represented by 0 which is then
   * replaced by the default value provided in the constructor.
   *
   * @var
   */
  public $default_value;


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
        watchdog('BAT', t('Event with @id, start date @start_date and end date @end_date was not added.', array('@id' => $event->value, '@start_date' => $event->startDateToString(), '@end_date' => $event->endDateToString())));
        break;
      }
      else {
        watchdog('BAT', t('Event with @id, start date @start_date and end date @end_date added.', array('@id' => $event->value, '@start_date' => $event->startDateToString(), '@end_date' => $event->endDateToString())));
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
   * @return array
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date) {
    $events = array();

    // We first get events in the itemized format
    $itemized_events = $this->getEventsItemized($start_date, $end_date);

    // We then normalize those events to create Events that get added to an array
    $events = $this->getEventsNormalized($start_date, $end_date, $itemized_events);

    return $events;
  }

  /**
   * Given a start and end time this will return the states units find themselves in for that range.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @return array
   *  An array of states keyed by unit
   */
  public function getStates(\DateTime $start_date, \DateTime $end_date) {
    $events = $this->getEvents($start_date, $end_date);
    $states = array();
    foreach ($events as $unit => $unit_events) {
      foreach ($unit_events as $event) {
        $states[$unit][$event->getValue()] = $event->getValue();
      }
    }

    return $states;
  }

  /**
   * Given a date range and a set of valid states it will return then units that are withing that
   * set of valid states.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $valid_states
   *
   * @return CalendarResponse
   */
  public function getMatchingUnits(\DateTime $start_date, \DateTime $end_date, $valid_states, $constraints) {
    $units = array();
    $response = new CalendarResponse($start_date, $end_date, $valid_states);
    $keyed_units = $this->keyUnitsById();

    $states = $this->getStates($start_date, $end_date);
    foreach ($states as $unit => $unit_states) {
      // Create an array with just the states
      $current_states = array_keys($unit_states);
      // Compare the current states with the set of valid states
      $remaining_states = array_diff($current_states, $valid_states);
      if (count($remaining_states) == 0 ) {
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
   * @param $store
   *
   * @return array
   */
  public function getEventsItemized(\DateTime $start_date, \DateTime $end_date) {
    // The final events we will return
    $events = array();

    $keyed_units = $this->keyUnitsById();

    $db_events = $this->store->getEventData($start_date, $end_date, array_keys($keyed_units));

    // Create a mock itemized event for the period in question - since event data is either
    // in the database or the default value we first create a mock event and then fill it in
    // accordingly
    $mock_event = new Event($start_date, $end_date, NULL, $this->default_value);
    $itemized = $mock_event->itemizeEvent();

    // Cycle through each unit retrieved and provide it with a fully configured itemized mock event
    foreach ($db_events as $unit => $event) {
      // Add the mock event
      $events[$unit] = $itemized;

      // Fill in month data coming from the database for our event
      foreach ($itemized[BAT_DAY] as $year => $months) {
        foreach ($months as $month => $days) {
          // Check if month is defined in DB otherwise set to default value
          if (isset($db_events[$unit][BAT_DAY][$year][$month])) {
            foreach ($days as $day => $value) {
              $events[$unit][BAT_DAY][$year][$month][$day] = ((int)$db_events[$unit][BAT_DAY][$year][$month][$day] == 0 ? $keyed_units[$unit]->getDefaultValue() : (int)$db_events[$unit][BAT_DAY][$year][$month][$day]);
            }
          }
          else {
            foreach ($days as $day => $value) {
              $events[$unit][BAT_DAY][$year][$month][$day] = $keyed_units[$unit]->getDefaultValue();
            }
          }

        }
      }

      // Fill in hour data coming from the database for our event that is represented
      // in the mock event
      foreach ($itemized[BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              if (isset($db_events[$unit][BAT_HOUR][$year][$month][$day][$hour])) {
                $events[$unit][BAT_HOUR][$year][$month]['d' . $day][$hour] = ((int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour] == 0 ? $keyed_units[$unit]->getDefaultValue() : (int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour]);
              }
              else {
                // If nothing from db - then revert to the defaults
                $events[$unit][BAT_HOUR][$year][$month][$day][$hour] = (int)$keyed_units[$unit]->getDefaultValue();
              }
            }
          }
        }
      }

      // Now fill in hour data coming from the database which the mock event did *not* cater for
      // but the mock event
      foreach ($db_events[$unit][BAT_HOUR] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $value) {
              $events[$unit][BAT_HOUR][$year][$month]['d'.$day][$hour] = ((int)$value == 0 ? $keyed_units[$unit]->getDefaultValue() : (int)$value);
            }
          }
        }
      }

      // Fill in minute data coming from the database for our event that is represented
      // in the mock event
      foreach ($itemized[BAT_MINUTE] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                if (isset($db_events[$unit][BAT_MINUTE][$year][$month][$day][$hour][$minute])) {
                  $events[$unit][BAT_MINUTE][$year][$month]['d' .$day]['h'.$hour][$minute] = ((int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour][$minute] == 0 ? $keyed_units[$unit]->getDefaultValue() : (int)$db_events[$unit][BAT_DAY][$year][$month][$day][$hour][$minute]);
                }
                else {
                  // If nothing from db - then revert to the defaults
                  $events[$unit][BAT_MINUTE][$year][$month][$day][$hour][$minute] = (int)$keyed_units[$unit]->getDefaultValue();
                }
              }
            }
          }
        }
      }

      // Now fill in minute data coming from the database which the mock event did *not* cater for
      foreach ($db_events[$unit][BAT_MINUTE] as $year => $months) {
        foreach ($months as $month => $days) {
          foreach ($days as $day => $hours) {
            foreach ($hours as $hour => $minutes) {
              foreach ($minutes as $minute => $value) {
                $events[$unit][BAT_MINUTE][$year][$month]['d'.$day]['h'.$hour][$minute] = ((int)$value == 0 ? $keyed_units[$unit]->getDefaultValue() : (int)$value);
              }
            }
          }
        }
      }

    }

    // Check to see if any events came back from the db
    if (count($events) == 0) {
      // If we don't have any db events add mock events (itemized)
      foreach ($keyed_units as $id => $unit) {
        $empty_event = new Event($start_date, $end_date, $id, $unit->getDefaultValue());
        $events[$id] = $empty_event->itemizeEvent();
      }
    }

    return $events;
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

    $normalized_events = array();

    $events_copy = $events;

    foreach ($events_copy as $unit => $data) {

      // Make sure years are sorted
      ksort($data[Event::BAT_DAY]);
      ksort($data[Event::BAT_HOUR]);
      ksort($data[Event::BAT_MINUTE]);

      // Set up variables to keep track of stuff
      $current_value = NULL;
      $start_event = NULL;
      $end_event = NULL;
      $event_value = NULL;
      $last_day = NULL;
      $last_hour = NULL;
      $last_minute = NULL;

      foreach ($data[Event::BAT_DAY] as $year => $months) {
        // Make sure months are in right order
        ksort($months);
        foreach ($months as $month => $days) {
          foreach ($days as $day => $value) {
            if ($value == -1) {
              // Retrieve hour data
              $hour_data = $events[$unit][Event::BAT_HOUR][$year][$month][$day];
              ksort($hour_data, SORT_NATURAL);
              foreach ($hour_data as $hour => $hour_value) {
                if ($hour_value == -1) {
                  // We are going to need minute values
                  $minute_data = $events[$unit][Event::BAT_MINUTE][$year][$month][$day][$hour];
                  ksort($minute_data, SORT_NATURAL);
                  foreach ($minute_data as $minute => $minute_value) {
                    if ($current_value === $minute_value) {
                      // We are still in minutes and going through so add a minute
                      $end_event->add(new \DateInterval('PT1M'));
                    }
                    elseif (($current_value != $minute_value) && ($current_value !== NULL)) {
                      // Value just switched - let us wrap up with current event and start a new one
                      $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
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
                  // Value just switched - let us wrap up with current event and start a new one
                  $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
                  // Start event becomes the end event with a minute added
                  $start_event = clone($end_event->add(new \DateInterval('PT1M')));
                  // End event comes the current point in time
                  $end_event = new \DateTime($year . '-' . $month . '-' . substr($day, 1) . ' ' . substr($hour, 1) . ':00');
                  $current_value = $hour_value;
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
              $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
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
      $normalized_events[$unit][] = new Event($start_event, $end_event, $unit, $current_value);
    }

    // Given the database structure we may get events that are not with the date ranges we were looking for
    // We get rid of them here so that the user has a clean result.
    foreach ($normalized_events as $unit => $events) {
      foreach ($events as $key => $event) {
        if ($event->inRange($start_date, $end_date)) {
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
          unset($normalized_events[$unit][$key]);
        }
      }
    }

    return $normalized_events;
  }

  /**
   * A simple utility funciton that given an array of datum=>value will group results based on
   * those that have the same value. Useful for grouping events based on state.
   *
   * @param $data
   * @param $length
   */
  public function groupData($data, $length) {
    // Given an array of the structure $date => $value we create another array
    // of structure $event, $length, $value
    // Cycle through day data and create events
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
  public function getUnitIds() {
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
  public function keyUnitsById() {
    $keyed_units = array();
    foreach ($this->units as $unit) {
      $keyed_units[$unit->getUnitId()] = $unit;
    }

    return $keyed_units;
  }

}
