<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;

use Roomify\Bat\Event\Event;

use Roomify\Bat\Calendar\Calendar;

use Roomify\Bat\Store\DrupalDBStore;

class CalendarTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test Calendar.
   */
  public function testCalendar() {
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-10 07:07');
    $state_store = new DrupalDBStore('availability_event', DrupalDBStore::BAT_STATE);
    $valid_states = array(5);

    $unit1 = new Unit(1, 2, array());
    $unit2 = new Unit(2, 2, array());

    $units = array($unit1, $unit2);

    $calendar = new Calendar($units, $state_store);

    $constraints = array();

    //$response = $calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    //$valid_unit_ids = array_keys($response->getIncluded());
  }

}
