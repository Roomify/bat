<?php

/**
 * @file
 * Class JsonStore
 */

namespace Roomify\Bat\Store;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Store\Store;

/**
 * This is a JSON implementation of the Store.
 *
 */
class JsonStore extends Store {

  // There are two types of stores - for event ids and status
  const BAT_EVENT = 'event';
  const BAT_STATE = 'state';

  /**
   * The file that holds day data.
   * @var
   */
  public $day_file;

  /**
   * The file that holds hour data.
   * @var
   */
  public $hour_file;

  /**
   * The file that holds minute data.
   * @var
   */
  public $minute_file;

  /**
   * The event type we are dealing with.
   * @var
   */
  public $event_type;


  /**
   * JsonStore constructor.
   *
   * Provided with the event type it will determine the appropriate file names to
   * store data in. This assumes standard behaviour from Bat_Event
   * @param $event_type
   * @param string $event_data
   */
  public function __construct($event_type, $event_data = 'state') {
    $this->event_type = $event_type;

    if ($event_data == JsonStore::BAT_STATE) {
      $this->day_file = 'build/' . $event_type . '_day_' . JsonStore::BAT_STATE . '.json';
      $this->hour_file = 'build/' . $event_type . '_hour_' . JsonStore::BAT_STATE . '.json';
      $this->minute_file = 'build/' . $event_type . '_minute_' . JsonStore::BAT_STATE . '.json';
    }

    if ($event_data == JsonStore::BAT_EVENT) {
      $this->day_file = 'build/' . $event_type . '_day_' . JsonStore::BAT_EVENT . '.json';
      $this->hour_file = 'build/' . $event_type . '_hour_' . JsonStore::BAT_EVENT . '.json';
      $this->minute_file = 'build/' . $event_type . '_minute_' . JsonStore::BAT_EVENT . '.json';
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

    $dayfile = (array)json_decode(file_get_contents($this->day_file));
    $hourfile = (array)json_decode(file_get_contents($this->hour_file));
    $minutefile = (array)json_decode(file_get_contents($this->minute_file));

    $events = array();

    // Cycle through day results and setup an event array
    foreach ($dayfile as $unit_id => $row) {
      foreach ($row as $year => $row2) {
        foreach ($row2 as $month => $row3) {
          foreach ($row3 as $day => $value) {
            $events[$unit_id][Event::BAT_DAY][$year][$month][$day] = $value;
          }
        }
      }
    }

    // With the day events taken care off let's cycle through hours
    foreach ($hourfile as $unit_id => $row) {
      foreach ($row as $year => $row2) {
        foreach ($row2 as $month => $row3) {
          foreach ($row3 as $day => $row4) {
            foreach ($row4 as $hour => $value) {
              $events[$unit_id][Event::BAT_HOUR][$year][$month][$day][$hour] = $value;
            }
          }
        }
      }
    }

    // With the hour events taken care off let's cycle through minutes
    foreach ($hourfile as $unit_id => $row) {
      foreach ($row as $year => $row2) {
        foreach ($row2 as $month => $row3) {
          foreach ($row3 as $day => $row4) {
            foreach ($row4 as $hour => $row5) {
              foreach ((array)$row5 as $min => $value) {
                $events[$unit_id][Event::BAT_MINUTE][$year][$month][$day][$hour][$min] = $value;
              }
            }
          }
        }
      }
    }

    return $events;
  }

  /**
   * @param \Roomify\Bat\Event\Event $event
   * @param $granularity
   *
   * @return bool
   */
  public function storeEvent(Event $event, $granularity = Event::BAT_HOURLY) {
    $stored = TRUE;

    $dayfile_content = (array)json_decode(file_get_contents($this->day_file));
    $hourfile_content = (array)json_decode(file_get_contents($this->hour_file));
    $minutefile_content = (array)json_decode(file_get_contents($this->minute_file));

    try {
      // Itemize an event so we can save it
      $itemized = $event->itemizeEvent($granularity);

      $dayfile_content[$event->unit_id] = $itemized[Event::BAT_DAY];

      // Write days
      $dayfile = fopen($this->day_file, 'w');
      fwrite($dayfile, json_encode($dayfile_content));
      fclose($dayfile);

      if ($granularity == Event::BAT_HOURLY) {
        $hourfile_content[$event->unit_id] = $itemized[Event::BAT_HOUR];

        // Write Hours
        $hourfile = fopen($this->hour_file, 'w');
        fwrite($hourfile, json_encode($hourfile_content));
        fclose($hourfile);

        $minutefile_content[$event->unit_id] = $itemized[Event::BAT_MINUTE];

        // Write minutes
        $minutefile = fopen($this->minute_file, 'w');
        fwrite($minutefile, json_encode($minutefile_content));
        fclose($minutefile);
      }
    }
    catch (\Exception $e) {
      $saved = FALSE;
    }

    return $stored;
  }

}
