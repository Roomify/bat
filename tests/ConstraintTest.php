<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;

use Roomify\Bat\Event\Event;

use Roomify\Bat\Calendar\Calendar;

use Roomify\Bat\Store\SqlDBStore;
use Roomify\Bat\Store\SqlLiteDBStore;

use Roomify\Bat\Constraint\MinMaxDaysConstraint;
use Roomify\Bat\Constraint\CheckInDayConstraint;

class ConstraintTest extends \PHPUnit_Extensions_Database_TestCase {

  protected $pdo = NULL;

  /**
   * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
   */
  public function getConnection() {
    if ($this->pdo === NULL) {
      $this->pdo = new \PDO('sqlite::memory:');
      $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

      $this->pdo->exec("CREATE TABLE bat_event_availability_event_day_event ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT '0', 'month' INTEGER NOT NULL DEFAULT '0', 'd1' INTEGER NOT NULL DEFAULT '0', 'd2' INTEGER NOT NULL DEFAULT '0', 'd3' INTEGER NOT NULL DEFAULT '0', 'd4' INTEGER NOT NULL DEFAULT '0', 'd5' INTEGER NOT NULL DEFAULT '0', 'd6' INTEGER NOT NULL DEFAULT '0', 'd7' INTEGER NOT NULL DEFAULT '0', 'd8' INTEGER NOT NULL DEFAULT '0', 'd9' INTEGER NOT NULL DEFAULT '0', 'd10' INTEGER NOT NULL DEFAULT '0', 'd11' INTEGER NOT NULL DEFAULT '0', 'd12' INTEGER NOT NULL DEFAULT '0', 'd13' INTEGER NOT NULL DEFAULT '0', 'd14' INTEGER NOT NULL DEFAULT '0', 'd15' INTEGER NOT NULL DEFAULT '0', 'd16' INTEGER NOT NULL DEFAULT '0', 'd17' INTEGER NOT NULL DEFAULT '0', 'd18' INTEGER NOT NULL DEFAULT '0', 'd19' INTEGER NOT NULL DEFAULT '0', 'd20' INTEGER NOT NULL DEFAULT '0', 'd21' INTEGER NOT NULL DEFAULT '0', 'd22' INTEGER NOT NULL DEFAULT '0', 'd23' INTEGER NOT NULL DEFAULT '0', 'd24' INTEGER NOT NULL DEFAULT '0', 'd25' INTEGER NOT NULL DEFAULT '0', 'd26' INTEGER NOT NULL DEFAULT '0', 'd27' INTEGER NOT NULL DEFAULT '0', 'd28' INTEGER NOT NULL DEFAULT '0', 'd29' INTEGER NOT NULL DEFAULT '0', 'd30' INTEGER NOT NULL DEFAULT '0', 'd31' INTEGER NOT NULL DEFAULT '0')");
      $this->pdo->exec("CREATE TABLE bat_event_availability_event_hour_event ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT '0', 'month' INTEGER NOT NULL DEFAULT '0', 'day' INTEGER NOT NULL DEFAULT '0', 'h0' INTEGER NOT NULL DEFAULT '0', 'h1' INTEGER NOT NULL DEFAULT '0', 'h2' INTEGER NOT NULL DEFAULT '0', 'h3' INTEGER NOT NULL DEFAULT '0', 'h4' INTEGER NOT NULL DEFAULT '0', 'h5' INTEGER NOT NULL DEFAULT '0', 'h6' INTEGER NOT NULL DEFAULT '0', 'h7' INTEGER NOT NULL DEFAULT '0', 'h8' INTEGER NOT NULL DEFAULT '0', 'h9' INTEGER NOT NULL DEFAULT '0', 'h10' INTEGER NOT NULL DEFAULT '0', 'h11' INTEGER NOT NULL DEFAULT '0', 'h12' INTEGER NOT NULL DEFAULT '0', 'h13' INTEGER NOT NULL DEFAULT '0', 'h14' INTEGER NOT NULL DEFAULT '0',  'h15' INTEGER NOT NULL DEFAULT '0', 'h16' INTEGER NOT NULL DEFAULT '0', 'h17' INTEGER NOT NULL DEFAULT '0', 'h18' INTEGER NOT NULL DEFAULT '0', 'h19' INTEGER NOT NULL DEFAULT '0', 'h20' INTEGER NOT NULL DEFAULT '0', 'h21' INTEGER NOT NULL DEFAULT '0', 'h22' INTEGER NOT NULL DEFAULT '0', 'h23' INTEGER NOT NULL DEFAULT '0')");
      $this->pdo->exec("CREATE TABLE bat_event_availability_event_minute_event ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT '0', 'month' INTEGER NOT NULL DEFAULT '0', 'day' INTEGER NOT NULL DEFAULT '0', 'hour' INTEGER NOT NULL DEFAULT '0', 'm00' INTEGER NOT NULL DEFAULT '0', 'm01' INTEGER NOT NULL DEFAULT '0', 'm02' INTEGER NOT NULL DEFAULT '0', 'm03' INTEGER NOT NULL DEFAULT '0', 'm04' INTEGER NOT NULL DEFAULT '0', 'm05' INTEGER NOT NULL DEFAULT '0', 'm06' INTEGER NOT NULL DEFAULT '0', 'm07' INTEGER NOT NULL DEFAULT '0', 'm08' INTEGER NOT NULL DEFAULT '0', 'm09' INTEGER NOT NULL DEFAULT '0', 'm10' INTEGER NOT NULL DEFAULT '0', 'm11' INTEGER NOT NULL DEFAULT '0', 'm12' INTEGER NOT NULL DEFAULT '0', 'm13' INTEGER NOT NULL DEFAULT '0', 'm14' INTEGER NOT NULL DEFAULT '0', 'm15' INTEGER NOT NULL DEFAULT '0', 'm16' INTEGER NOT NULL DEFAULT '0', 'm17' INTEGER NOT NULL DEFAULT '0', 'm18' INTEGER NOT NULL DEFAULT '0', 'm19' INTEGER NOT NULL DEFAULT '0', 'm20' INTEGER NOT NULL DEFAULT '0', 'm21' INTEGER NOT NULL DEFAULT '0', 'm22' INTEGER NOT NULL DEFAULT '0', 'm23' INTEGER NOT NULL DEFAULT '0', 'm24' INTEGER NOT NULL DEFAULT '0', 'm25' INTEGER NOT NULL DEFAULT '0', 'm26' INTEGER NOT NULL DEFAULT '0', 'm27' INTEGER NOT NULL DEFAULT '0', 'm28' INTEGER NOT NULL DEFAULT '0', 'm29' INTEGER NOT NULL DEFAULT '0', 'm30' INTEGER NOT NULL DEFAULT '0', 'm31' INTEGER NOT NULL DEFAULT '0', 'm32' INTEGER NOT NULL DEFAULT '0', 'm33' INTEGER NOT NULL DEFAULT '0', 'm34' INTEGER NOT NULL DEFAULT '0', 'm35' INTEGER NOT NULL DEFAULT '0', 'm36' INTEGER NOT NULL DEFAULT '0', 'm37' INTEGER NOT NULL DEFAULT '0', 'm38' INTEGER NOT NULL DEFAULT '0', 'm39' INTEGER NOT NULL DEFAULT '0', 'm40' INTEGER NOT NULL DEFAULT '0', 'm41' INTEGER NOT NULL DEFAULT '0', 'm42' INTEGER NOT NULL DEFAULT '0', 'm43' INTEGER NOT NULL DEFAULT '0', 'm44' INTEGER NOT NULL DEFAULT '0', 'm45' INTEGER NOT NULL DEFAULT '0', 'm46' INTEGER NOT NULL DEFAULT '0', 'm47' INTEGER NOT NULL DEFAULT '0', 'm48' INTEGER NOT NULL DEFAULT '0', 'm49' INTEGER NOT NULL DEFAULT '0', 'm50' INTEGER NOT NULL DEFAULT '0', 'm51' INTEGER NOT NULL DEFAULT '0', 'm52' INTEGER NOT NULL DEFAULT '0', 'm53' INTEGER NOT NULL DEFAULT '0', 'm54' INTEGER NOT NULL DEFAULT '0', 'm55' INTEGER NOT NULL DEFAULT '0', 'm56' INTEGER NOT NULL DEFAULT '0', 'm57' INTEGER NOT NULL DEFAULT '0', 'm58' INTEGER NOT NULL DEFAULT '0', 'm59' INTEGER NOT NULL DEFAULT '0')");

      $this->pdo->exec("CREATE TABLE bat_event_availability_event_day_state ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT '0', 'month' INTEGER NOT NULL DEFAULT '0', 'd1' INTEGER NOT NULL DEFAULT '0', 'd2' INTEGER NOT NULL DEFAULT '0', 'd3' INTEGER NOT NULL DEFAULT '0', 'd4' INTEGER NOT NULL DEFAULT '0', 'd5' INTEGER NOT NULL DEFAULT '0', 'd6' INTEGER NOT NULL DEFAULT '0', 'd7' INTEGER NOT NULL DEFAULT '0', 'd8' INTEGER NOT NULL DEFAULT '0', 'd9' INTEGER NOT NULL DEFAULT '0', 'd10' INTEGER NOT NULL DEFAULT '0', 'd11' INTEGER NOT NULL DEFAULT '0', 'd12' INTEGER NOT NULL DEFAULT '0', 'd13' INTEGER NOT NULL DEFAULT '0', 'd14' INTEGER NOT NULL DEFAULT '0', 'd15' INTEGER NOT NULL DEFAULT '0', 'd16' INTEGER NOT NULL DEFAULT '0', 'd17' INTEGER NOT NULL DEFAULT '0', 'd18' INTEGER NOT NULL DEFAULT '0', 'd19' INTEGER NOT NULL DEFAULT '0', 'd20' INTEGER NOT NULL DEFAULT '0', 'd21' INTEGER NOT NULL DEFAULT '0', 'd22' INTEGER NOT NULL DEFAULT '0', 'd23' INTEGER NOT NULL DEFAULT '0', 'd24' INTEGER NOT NULL DEFAULT '0', 'd25' INTEGER NOT NULL DEFAULT '0', 'd26' INTEGER NOT NULL DEFAULT '0', 'd27' INTEGER NOT NULL DEFAULT '0', 'd28' INTEGER NOT NULL DEFAULT '0', 'd29' INTEGER NOT NULL DEFAULT '0', 'd30' INTEGER NOT NULL DEFAULT '0', 'd31' INTEGER NOT NULL DEFAULT '0')");
      $this->pdo->exec("CREATE TABLE bat_event_availability_event_hour_state ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT '0', 'month' INTEGER NOT NULL DEFAULT '0', 'day' INTEGER NOT NULL DEFAULT '0', 'h0' INTEGER NOT NULL DEFAULT '0', 'h1' INTEGER NOT NULL DEFAULT '0', 'h2' INTEGER NOT NULL DEFAULT '0', 'h3' INTEGER NOT NULL DEFAULT '0', 'h4' INTEGER NOT NULL DEFAULT '0', 'h5' INTEGER NOT NULL DEFAULT '0', 'h6' INTEGER NOT NULL DEFAULT '0', 'h7' INTEGER NOT NULL DEFAULT '0', 'h8' INTEGER NOT NULL DEFAULT '0', 'h9' INTEGER NOT NULL DEFAULT '0', 'h10' INTEGER NOT NULL DEFAULT '0', 'h11' INTEGER NOT NULL DEFAULT '0', 'h12' INTEGER NOT NULL DEFAULT '0', 'h13' INTEGER NOT NULL DEFAULT '0', 'h14' INTEGER NOT NULL DEFAULT '0',  'h15' INTEGER NOT NULL DEFAULT '0', 'h16' INTEGER NOT NULL DEFAULT '0', 'h17' INTEGER NOT NULL DEFAULT '0', 'h18' INTEGER NOT NULL DEFAULT '0', 'h19' INTEGER NOT NULL DEFAULT '0', 'h20' INTEGER NOT NULL DEFAULT '0', 'h21' INTEGER NOT NULL DEFAULT '0', 'h22' INTEGER NOT NULL DEFAULT '0', 'h23' INTEGER NOT NULL DEFAULT '0')");
      $this->pdo->exec("CREATE TABLE bat_event_availability_event_minute_state ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT '0', 'month' INTEGER NOT NULL DEFAULT '0', 'day' INTEGER NOT NULL DEFAULT '0', 'hour' INTEGER NOT NULL DEFAULT '0', 'm00' INTEGER NOT NULL DEFAULT '0', 'm01' INTEGER NOT NULL DEFAULT '0', 'm02' INTEGER NOT NULL DEFAULT '0', 'm03' INTEGER NOT NULL DEFAULT '0', 'm04' INTEGER NOT NULL DEFAULT '0', 'm05' INTEGER NOT NULL DEFAULT '0', 'm06' INTEGER NOT NULL DEFAULT '0', 'm07' INTEGER NOT NULL DEFAULT '0', 'm08' INTEGER NOT NULL DEFAULT '0', 'm09' INTEGER NOT NULL DEFAULT '0', 'm10' INTEGER NOT NULL DEFAULT '0', 'm11' INTEGER NOT NULL DEFAULT '0', 'm12' INTEGER NOT NULL DEFAULT '0', 'm13' INTEGER NOT NULL DEFAULT '0', 'm14' INTEGER NOT NULL DEFAULT '0', 'm15' INTEGER NOT NULL DEFAULT '0', 'm16' INTEGER NOT NULL DEFAULT '0', 'm17' INTEGER NOT NULL DEFAULT '0', 'm18' INTEGER NOT NULL DEFAULT '0', 'm19' INTEGER NOT NULL DEFAULT '0', 'm20' INTEGER NOT NULL DEFAULT '0', 'm21' INTEGER NOT NULL DEFAULT '0', 'm22' INTEGER NOT NULL DEFAULT '0', 'm23' INTEGER NOT NULL DEFAULT '0', 'm24' INTEGER NOT NULL DEFAULT '0', 'm25' INTEGER NOT NULL DEFAULT '0', 'm26' INTEGER NOT NULL DEFAULT '0', 'm27' INTEGER NOT NULL DEFAULT '0', 'm28' INTEGER NOT NULL DEFAULT '0', 'm29' INTEGER NOT NULL DEFAULT '0', 'm30' INTEGER NOT NULL DEFAULT '0', 'm31' INTEGER NOT NULL DEFAULT '0', 'm32' INTEGER NOT NULL DEFAULT '0', 'm33' INTEGER NOT NULL DEFAULT '0', 'm34' INTEGER NOT NULL DEFAULT '0', 'm35' INTEGER NOT NULL DEFAULT '0', 'm36' INTEGER NOT NULL DEFAULT '0', 'm37' INTEGER NOT NULL DEFAULT '0', 'm38' INTEGER NOT NULL DEFAULT '0', 'm39' INTEGER NOT NULL DEFAULT '0', 'm40' INTEGER NOT NULL DEFAULT '0', 'm41' INTEGER NOT NULL DEFAULT '0', 'm42' INTEGER NOT NULL DEFAULT '0', 'm43' INTEGER NOT NULL DEFAULT '0', 'm44' INTEGER NOT NULL DEFAULT '0', 'm45' INTEGER NOT NULL DEFAULT '0', 'm46' INTEGER NOT NULL DEFAULT '0', 'm47' INTEGER NOT NULL DEFAULT '0', 'm48' INTEGER NOT NULL DEFAULT '0', 'm49' INTEGER NOT NULL DEFAULT '0', 'm50' INTEGER NOT NULL DEFAULT '0', 'm51' INTEGER NOT NULL DEFAULT '0', 'm52' INTEGER NOT NULL DEFAULT '0', 'm53' INTEGER NOT NULL DEFAULT '0', 'm54' INTEGER NOT NULL DEFAULT '0', 'm55' INTEGER NOT NULL DEFAULT '0', 'm56' INTEGER NOT NULL DEFAULT '0', 'm57' INTEGER NOT NULL DEFAULT '0', 'm58' INTEGER NOT NULL DEFAULT '0', 'm59' INTEGER NOT NULL DEFAULT '0')");
    }

    return $this->createDefaultDBConnection($this->pdo, ':memory:');
  }

  /**
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   */
  public function getDataSet() {
    return $this->createMySQLXMLDataSet(dirname(__FILE__) . '/_files/events.xml');
  }

  /**
   * Test Constraint.
   */
  public function testMinMaxDaysConstraint() {
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-04 07:07');

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

    $valid_states = array(0, 4, 5);

    $minmax_constraint = new MinMaxDaysConstraint(array($unit1), 5);
    $constraints = array($minmax_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(2));

    $minmax_constraint = new MinMaxDaysConstraint(array($unit2), 5);
    $constraints = array($minmax_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(1));
  }

  /**
   * Test Constraint.
   */
  public function testCheckInDayConstraint() {
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-04 07:07');

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

    $valid_states = array(0, 4, 5);

    $minmax_constraint = new CheckInDayConstraint(array($unit1), 4);
    $constraints = array($minmax_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(2));

    $minmax_constraint = new CheckInDayConstraint(array($unit2), 5);
    $constraints = array($minmax_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(1, 2));
  }

}
