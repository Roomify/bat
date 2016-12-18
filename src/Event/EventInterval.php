<?php

/**
 * @file
 * Class EventInterval
 */

namespace Roomify\Bat\Event;

/**
 * Class EventInterval
 *
 * A utility class that brings together functions we end up using often across Bat to hand Event Intervals.
 *
 * @package Roomify\Bat\Event
 */
class EventInterval {

  /**
   * Return the number of times a duration fits into the start and end date taking into account
   * BAT's consideration that the end time for a BAT event includes that last minute.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param \DateInterval $duration
   *
   * @return float
   */
  public static function divide(\DateTime $start_date, \DateTime $end_date, \DateInterval $duration) {
    // Clone so that we don't change the original object. We are not using ImmutableDateTime to support PHP5.4
    $temp_end_date = clone($end_date);

    // Add a minute to deal with the fact that BAT considers the last minute included
    $temp_end_date->add(new \DateInterval('PT1M'));

    // Convert everything to seconds and calculate exactly how many times the duration fits in our event length
    $duration_seconds = $duration->d * 86400 + $duration->h * 3600 + $duration->i * 60 + $duration->s;

    $diff = $start_date->diff($temp_end_date);
    $diff_seconds = $diff->days * 86400 + $diff->h * 3600 + $diff->i * 60 + $diff->s;

    return $diff_seconds / $duration_seconds;
  }

}
