<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\Calendar;

class CalendarTest extends \PHPUnit_Extensions_Database_TestCase {

  /**
   * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  public function getConnection()
  {
    $pdo = new \PDO('sqlite::memory:');
    return $this->createDefaultDBConnection($pdo, ':memory:');
  }

  /**
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  public function getDataSet()
  {
    return $this->createMySQLXMLDataSet(dirname(__FILE__).'/_files/events.xml');
  }


  /**
   * Test Calendar.
   */
  public function testCalendar() {
    /*$start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-10 07:07');

    $state_store = new JsonStore('availability_event', JsonStore::BAT_STATE);
    $event_store = new JsonStore('availability_event', JsonStore::BAT_EVENT);

    $valid_states = array(0, 2, 4, 5);

    $unit1 = new Unit(1, 2, array());
    $unit2 = new Unit(2, 2, array());

    $units = array($unit1);

    $state_calendar = new Calendar($units, $state_store);
    $event_calendar = new Calendar($units, $event_store);

    $state_event = new Event($start_date, $end_date, $unit1->getUnitId(), 4);
    $event_id_event = new Event($start_date, $end_date, $unit1->getUnitId(), 2);

    $state_calendar->addEvents(array($state_event), Event::BAT_HOURLY);
    $event_calendar->addEvents(array($event_id_event), Event::BAT_HOURLY);

    $constraints = array();

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    var_dump($valid_unit_ids);*/
  }

}
