<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\SqlDBStore;
use Roomify\Bat\Store\SqlLiteDBStore;

class CalendarTest extends \PHPUnit_Framework_TestCase {

  protected $pdo = NULL;

  public function setUp() {
    if ($this->pdo === NULL) {
      $this->pdo = new \PDO('sqlite::memory:');
      $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      // Create tables
      $this->pdo->exec(SetupStore::createDayTable('availability_event', 'event'));
      $this->pdo->exec(SetupStore::createDayTable('availability_event', 'state'));
      $this->pdo->exec(SetupStore::createHourTable('availability_event', 'event'));
      $this->pdo->exec(SetupStore::createHourTable('availability_event', 'state'));
      $this->pdo->exec(SetupStore::createMinuteTable('availability_event', 'event'));
      $this->pdo->exec(SetupStore::createMinuteTable('availability_event', 'state'));

    }

  }


  /**
   * Test Calendar.
   */
  public function testCalendar() {
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-10 07:07');

    $state_store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);
    $event_store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_EVENT);

    $unit1 = new Unit(1, 2, array());
    $unit2 = new Unit(2, 2, array());

    $units = array($unit1, $unit2);

    $state_calendar = new Calendar($units, $state_store);
    $event_calendar = new Calendar($units, $event_store);

    $state_event1 = new Event($start_date, $end_date, $unit1->getUnitId(), 4);
    $event_id_event1 = new Event($start_date, $end_date, $unit1->getUnitId(), 2);

    $state_calendar->addEvents(array($state_event1), Event::BAT_HOURLY);
    $event_calendar->addEvents(array($event_id_event1), Event::BAT_HOURLY);

    $state_event2 = new Event($start_date, $end_date, $unit2->getUnitId(), 5);
    $event_id_event2 = new Event($start_date, $end_date, $unit2->getUnitId(), 3);

    $state_calendar->addEvents(array($state_event2), Event::BAT_HOURLY);
    $event_calendar->addEvents(array($event_id_event2), Event::BAT_HOURLY);

    $valid_states = array(0, 4);
    $constraints = array();

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(1));

    $valid_states = array(0, 4, 5);
    $constraints = array();

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(1, 2));
  }

}
