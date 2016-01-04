<?php

/**
 * @file
 * Interface CalendarInterface
 */

namespace Roomify\Bat\Calendar;

/**
 * Handles querying and updating the availability information
 * relative to a single bookable unit.
 */

interface CalendarInterface {

  /**
   * Given a date range returns an array of Events keyed by unit id.
   *
   * @param $start_date
   * The starting date
   *
   * @param $end_date
   * The end date of our range
   *
   * @return EventInterface[]
   * An array of Event objects
   */
  public function getEvents(\DateTime $start_date, \DateTime $end_date);

  /**
   * Given an array of Events the calendar is updated with the relevant data.
   *
   * @param EventInterface[] $events
   *   An array of events to update the calendar with
   *
   * @param granularity
   *  The leverl of detail (one of HOURLY, DAILY) at which to store the event
   */
  public function addEvents($events, $granularity);

}
