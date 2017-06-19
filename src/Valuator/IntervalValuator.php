<?php

/**
 * @file
 * Class IntervalValuator
 */

namespace Roomify\Bat\Valuator;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\Store;
use Roomify\Bat\Valuator\AbstractValuator;
use Roomify\Bat\Unit\UnitInterface;
use Roomify\Bat\Event\EventInterval;

/**
 * The IntervalValuator sums the aggregate value of an event by dividing time
 * in discreet intervals and then assigning value to those
 * intervals based on the value the unit has during that interval.
 *
 * For example, if we are dealing with a hotel room and want to calculate nightly rates
 * we can set the date interval to P1D. We will query the calendar for events whose value
 * represents prices over that period and the split the time in 1-day intervals and sum up
 * accordingly.
 *
 * If we are selling activities at 15m intervals we can set the interval at PT15M and we would
 * be splitting events on 15m intervals and would assign the value of each interval to the value
 * of the event during that interval.
 */
class IntervalValuator extends AbstractValuator {

  /**
   * @var \DateInterval
   */
  protected $duration_unit;

  /**
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param UnitInterface $unit
   * @param Store $store
   * @param \DateInterval $duration_unit
   */
  public function __construct(\DateTime $start_date, \DateTime $end_date, UnitInterface $unit, Store $store, \DateInterval $duration_unit) {
    parent::__construct($start_date, $end_date, $unit, $store);
    $this->duration_unit = $duration_unit;
  }

  /**
   * {@inheritdoc}
   */
  public function determineValue() {
    $value = 0;

    // Instantiate a calendar
    $calendar = new Calendar(array($this->unit), $this->store);

    $events = $calendar->getEvents($this->start_date, $this->end_date);

    foreach ($events as $unit => $unit_events) {
      if ($unit == $this->unit->getUnitId()) {
        foreach ($unit_events as $event) {
          $percentage = EventInterval::divide($event->getStartDate(), $event->getEndDate(), $this->duration_unit);
          $value = $value + $event->getValue() * $percentage;
        }
      }
    }

    return round($value, 2);
  }

}
