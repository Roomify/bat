<?php

/**
 * @file
 */

namespace Roomify\Bat\Valuator;

use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\Store;
use Roomify\Bat\Valuator\AbstractValuator;
use Roomify\Bat\Unit\Unit;

/**
 * The IntervalValuator sums the aggregate value of an event by dividing time
 * in discreet intervals (using \DateInterval) and then assigning value to those
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
 *
 * Class IntervalValuator
 * @package Roomify\Bat\Valuator
 */
class IntervalValuator extends AbstractValuator {

  protected $duration_unit;

  public function __construct(\DateTime $start_date, \DateTime $end_date, Unit $unit, Store $store, \DateInterval $duration_unit) {
    parent::__construct($start_date, $end_date, $unit, $store);
    $this->duration_unit = $duration_unit;
  }

  public function determineValue() {

    $value = 0;

    // Instantiate a calendar
    $calendar = new Calendar(array($this->unit), $this->store);

    $events = $calendar->getEvents($this->start_date, $this->end_date);

    foreach ($events as $unit => $unit_events) {
      // Create a period with the duration and dates supplied
      $period = new \DatePeriod($this->start_date, $this->duration_unit, $this->end_date);
      print_r("NEW CYCLE\n");
      //var_dump($period);
      //var_dump($unit);
      //var_dump($unit_events);
      foreach ($unit_events as $event){
        if ($unit == $this->unit->getUnitId()){
          foreach ($period as $dt) {
              // If event in period involved add value of event
              // If event is not completely in period involved then add just the percentage that is
            print_r("Event is: ");
            print_r($event->getStartDate()->format('Y-m-d H:i:s') ." to ");
            print_r($event->getEndDate()->format('Y-m-d H:i:s') ."\n");
            print_r("Period is: ");
            print_r($dt->format('Y-m-d H:i:s') ."\n");
            if ($event->dateIsInRange($dt)){
              print_r("In range adding: " . $event->getValue() . " to event.\n");
              $value = $value + $event->getValue();
              print_r("Current Value " .$value . "\n");
            }
          }
        }
      }
    }
    return $value;
  }

}