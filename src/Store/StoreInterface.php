<?php

/**
 * @file
 * Interface StoreInterface
 */

namespace Roomify\Bat\Store;

use Roomify\Bat\Event\EventInterface;

/**
 * A store is a place where event data is held. The purpose of separating these
 * classes is so as to isolate (currently) Drupal-specific code and to allow for
 * other stores to be introduced.
 */
interface StoreInterface {

  /**
   * Given a data range returns events keyed by unit_id.
   *
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param $unit_ids
   *
   * @return array
   */
  public function getEventData(\DateTime $start_date, \DateTime $end_date, $unit_ids);

  /**
   * Given an event it will save it and return true if successful.
   *
   * @param \Roomify\Bat\Event\EventInterface $event
   * @param $granularity
   *
   * @return boolean
   */
  public function storeEvent(EventInterface $event, $granularity);

}
