<?php

namespace Roomify\Bat\Test;

use PHPUnit\DbUnit\TestCase;

use Roomify\Bat\Unit\Unit;

use Roomify\Bat\Event\Event;

use Roomify\Bat\Calendar\Calendar;

use Roomify\Bat\Store\SqlDBStore;
use Roomify\Bat\Store\SqlLiteDBStore;

use Roomify\Bat\Constraint\ConstraintManager;
use Roomify\Bat\Constraint\MinMaxDaysConstraint;
use Roomify\Bat\Constraint\CheckInDayConstraint;
use Roomify\Bat\Constraint\DateConstraint;

class ConstraintTest extends TestCase {

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

    $state_event1 = new Event($start_date, $end_date, $unit1, 4);
    $event_id_event1 = new Event($start_date, $end_date, $unit1, 2);

    $state_calendar->addEvents(array($state_event1), Event::BAT_HOURLY);
    $event_calendar->addEvents(array($event_id_event1), Event::BAT_HOURLY);

    $state_event2 = new Event($start_date, $end_date, $unit2, 5);
    $event_id_event2 = new Event($start_date, $end_date, $unit2, 3);

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

    $state_event1 = new Event($start_date, $end_date, $unit1, 4);
    $event_id_event1 = new Event($start_date, $end_date, $unit1, 2);

    $state_calendar->addEvents(array($state_event1), Event::BAT_HOURLY);
    $event_calendar->addEvents(array($event_id_event1), Event::BAT_HOURLY);

    $state_event2 = new Event($start_date, $end_date, $unit2, 5);
    $event_id_event2 = new Event($start_date, $end_date, $unit2, 3);

    $state_calendar->addEvents(array($state_event2), Event::BAT_HOURLY);
    $event_calendar->addEvents(array($event_id_event2), Event::BAT_HOURLY);

    $valid_states = array(0, 4, 5);

    $checkinday_constraint = new CheckInDayConstraint(array($unit1), 4);
    $constraints = array($checkinday_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(2));

    $checkinday_constraint = new CheckInDayConstraint(array($unit2), 5);
    $constraints = array($checkinday_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(1, 2));
  }

  /**
   * Test Constraint.
   */
  public function testDateConstraint() {
    $start_date = new \DateTime('2016-01-02 12:12');
    $end_date = new \DateTime('2016-01-04 07:07');

    $state_store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);
    $event_store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_EVENT);

    $unit1 = new Unit(1, 2, array());
    $unit2 = new Unit(2, 2, array());

    $units = array($unit1, $unit2);

    $state_calendar = new Calendar($units, $state_store);
    $event_calendar = new Calendar($units, $event_store);

    $sd1 = new \DateTime('2016-01-01 12:12');
    $ed1 = new \DateTime('2016-01-05 13:12');
    $date_constraint = new DateConstraint(array(), $sd1, $ed1);
    $constraints = array($date_constraint);

    $valid_states = array(2);
    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array(1, 2));

    $sd1 = new \DateTime('2015-01-02 13:12');
    $ed1 = new \DateTime('2016-01-03 13:12');
    $date_constraint = new DateConstraint(array(), $sd1, $ed1);
    $constraints = array($date_constraint);

    $response = $state_calendar->getMatchingUnits($start_date, $end_date, $valid_states, $constraints);
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids, array());
  }

  /**
   * Test to String on Checkin Constraint.
   */
  public function testToStringCheckInConstraint() {
    $u1 = new Unit(1,10,array());

    $checkin_day = 1;

    $units = array($u1);

    $sd = new \DateTime('2016-01-01 12:12');
    $ed = new \DateTime('2016-03-31 18:12');

    $sd1 = new \DateTime('2016-01-02 12:12');
    $ed1 = new \DateTime('2016-01-10 13:12');

    // Create an event for unit 1.
    $e1u1 = new Event($sd1, $ed1, $u1, 11);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1), Event::BAT_HOURLY);

    // Constraint with Dates
    $checkinday_constraint = new CheckInDayConstraint(array($u1), $checkin_day, $sd, $ed);
    $string = $checkinday_constraint->toString();
    $this->assertEquals($string['text'], 'From @start_date to @end_date, bookings must start on @day_of_the_week');
    $this->assertEquals($string['args']['@start_date'], '2016-01-01');
    $this->assertEquals($string['args']['@end_date'], '2016-03-31');
    $this->assertEquals($string['args']['@day_of_the_week'], 'Monday');
    $constraints = array($checkinday_constraint);

    // Constraint without Dates
    $checkinday_constraint = new CheckInDayConstraint(array($u1), $checkin_day);
    $string = $checkinday_constraint->toString();

    $this->assertEquals($string['text'], 'Bookings must start on @day_of_the_week');
    $this->assertEquals($string['args']['@day_of_the_week'], 'Monday');

    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11), array());
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids[0], 1);

    // Applying the constraint the unit 1 should be no longer valid.
    $response->applyConstraints($constraints);
    $invalid_unit_ids = array_keys($response->getExcluded());
    $this->assertEquals($invalid_unit_ids[0], 1);

  }

  /**
   * Test to String on Date Constraint.
   */
  public function testToStringDateConstraint() {
    $u1 = new Unit(1,10,array());

    $checkin_day = 1;

    $units = array($u1);

    // Imagine we are testing on 2016-01-01, and do not allow creating an event
    // until 2 days from now, and not after six months.
    $sd = new \DateTime('2016-01-03 00:00');
    $ed = new \DateTime('2016-06-01 00:00');

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Constraint with Dates
    $date_constraint = new DateConstraint(array($u1), $sd, $ed);
    $string = $date_constraint->toString();
    $this->assertEquals($string['text'], 'From @start_date to @end_date');
    $this->assertEquals($string['args']['@start_date'], '2016-01-03');
    $this->assertEquals($string['args']['@end_date'], '2016-06-01');
    $constraints = array($date_constraint);

    // Test an event starting on the second.
    $sd1 = new \DateTime('2016-01-02 12:00');
    $ed1 = new \DateTime('2016-01-10 13:12');

    $response = $calendar->getMatchingUnits($sd1, $ed1, array(10, 11), array());
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids[0], 1);

    // Applying the constraint the unit 1 should be no longer valid.
    $response->applyConstraints($constraints);
    $invalid_unit_ids = array_keys($response->getExcluded());
    $this->assertEquals($invalid_unit_ids[0], 1);

    // Test an event ending in July.
    $sd1 = new \DateTime('2016-05-02 12:00');
    $ed1 = new \DateTime('2016-07-10 13:12');

    $response = $calendar->getMatchingUnits($sd1, $ed1, array(10, 11), array());
    $valid_unit_ids = array_keys($response->getIncluded());
    $this->assertEquals($valid_unit_ids[0], 1);

    // Applying the constraint the unit 1 should be no longer valid.
    $response->applyConstraints($constraints);
    $invalid_unit_ids = array_keys($response->getExcluded());
    $this->assertEquals($invalid_unit_ids[0], 1);
  }

  /**
   * Test to String on Min Max Days Constraint.
   */
  public function testToStringMinMaxDaysConstraint() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-01-01 15:10');
    $ed = new \DateTime('2016-06-30 18:00');

    $sd1 = new \DateTime('2016-01-07 02:12');
    $ed1 = new \DateTime('2016-01-13 13:12');

     // Create some events for units 1,2,3
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 13);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1, $e1u2), Event::BAT_HOURLY);

    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 13), array());
    $valid_unit_ids = array_keys($response->getIncluded());

    // Unit 1 and 2 should be inside valid unit ids.
    $this->assertEquals($valid_unit_ids[0], 1);
    $this->assertEquals($valid_unit_ids[1], 2);

    // Add the constraint with Start and End dates.
    $minmax_constraint = new MinMaxDaysConstraint(array($u1), 15, 0, $sd, $ed);
    // Test the toString() method.
    $string = $minmax_constraint->toString();
    $constraints = array($minmax_constraint);
    // Check the string;
    $this->assertEquals($string['text'], 'From @start_date to @end_date the stay must be for at least @minimum_stay');
    $response->applyConstraints($constraints);
    // Now Unit 1 should be not valid.
    $valid_unit_ids = array_keys($response->getIncluded());
    $invalid_unit_ids = array_keys($response->getExcluded());

    $this->assertEquals($valid_unit_ids[0], 2);
    $this->assertEquals($invalid_unit_ids[0], 1);

    // Add the constraint without Start and End dates.
    $minmax_constraint = new MinMaxDaysConstraint(array($u2), 15);
    // Test the toString() method.
    $string = $minmax_constraint->toString();
    // Check the string.
    $this->assertEquals($string['text'], 'The stay must be for at least @minimum_stay');
  
    $constraints = array($minmax_constraint);
    $response->applyConstraints($constraints);
    // Now Unit 2 should be not valid too.
    $valid_unit_ids = array_keys($response->getIncluded());
    $invalid_unit_ids = array_keys($response->getExcluded());

    $this->assertEquals($invalid_unit_ids[0], 1);
    $this->assertEquals($invalid_unit_ids[1], 2);

    // Recreate Response without constraints.
    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 13), array());
  
    // Add A constraints with the checkin day.
    $minmax_constraint = new MinMaxDaysConstraint(array($u1), 3, 0, $sd, $ed, 5);
    // Test the toString() method.
    $string = $minmax_constraint->toString();
    $this->assertEquals($string['text'], 'From @start_date to @end_date, if booking starts on @day_of_the_week the stay must be for at least @minimum_stay');
    $constraints = array($minmax_constraint);
    $response->applyConstraints($constraints);
    $valid_unit_ids = array_keys($response->getIncluded());

    $minmax_constraint = new MinMaxDaysConstraint(array($u2), 3, 3, NULL, NULL, 4);
    // Test the toString() method.
    $string = $minmax_constraint->toString();

    $this->assertEquals($string['text'], 'If booking starts on @day_of_the_week the stay must be for @minimum_stay');
    $constraints = array($minmax_constraint);
    $response->applyConstraints($constraints);

    // Recreate Response without constraints.
    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 13), array());
    // Add A constraints with max days of stay.
    $minmax_constraint = new MinMaxDaysConstraint(array($u1), 0, 4, $sd, $ed);
    // Test the toString() method.
    $string = $minmax_constraint->toString();
    $this->assertEquals($string['text'], 'From @start_date to @end_date the stay cannot be more than @maximum_stay');


    // Recreate Response without constraints.
    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 13), array());
    // Add A constraints with min and max days of stay
    $minmax_constraint = new MinMaxDaysConstraint(array($u1), 1, 4, $sd, $ed);
    // Test the toString() method.
    $string = $minmax_constraint->toString();
    $this->assertEquals($string['text'], 'From @start_date to @end_date the stay must be at least @minimum_stay and at most @maximum_stay');
  }

  /**
   * Test to Constraints Base functionality.
   */
  public function testConstraintsBaseFunctions() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-01-01 15:10');
    $ed = new \DateTime('2016-06-30 18:00');

    $sd1 = new \DateTime('2016-01-07 02:12');
    $ed1 = new \DateTime('2016-01-13 13:12');

     // Create some events for units 1,2
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 13);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1, $e1u2), Event::BAT_HOURLY);

    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 13), array());

    // Add the constraint with Start and End dates.
    $constraint = new MinMaxDaysConstraint(array($u1, $u2), 3, 0, $sd, $ed);
    $constraint->setStartDate($sd1);
    $this->assertEquals($constraint->getStartDate(), $sd1);

    $constraint->setEndDate($ed1);
    $this->assertEquals($constraint->getEndDate(), $ed1);

    $constraint->getAffectedUnits();
  }

  public function testConstraintManager() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-01-01 00:00');
    $ed = new \DateTime('2017-01-01 00:00');

    $sd1 = new \DateTime('2016-02-01 00:00');
    $ed1 = new \DateTime('2016-04-01 00:00');

    $sd2 = new \DateTime('2016-03-01 00:00');
    $ed2 = new \DateTime('2016-03-10 00:00');

    $constraints = array();
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 3, 0, $sd, $ed);
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 4, 0, $sd1, $ed1);
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 5, 0, $sd2, $ed2);

    $constraint_manager = new ConstraintManager($constraints);
    $normalized_constraints = $constraint_manager->normalizeConstraints('Roomify\Bat\Constraint\MinMaxDaysConstraint');

    $this->assertEquals(count($constraints), 3);
    $this->assertEquals(count($normalized_constraints), 5);

    $this->assertEquals($normalized_constraints[0]->getStartDate()->format('Y-m-d'), '2016-03-01');
    $this->assertEquals($normalized_constraints[0]->getEndDate()->format('Y-m-d'), '2016-03-10');

    $this->assertEquals($normalized_constraints[1]->getStartDate()->format('Y-m-d'), '2016-03-11');
    $this->assertEquals($normalized_constraints[1]->getEndDate()->format('Y-m-d'), '2016-04-01');

    $this->assertEquals($normalized_constraints[2]->getStartDate()->format('Y-m-d'), '2016-02-01');
    $this->assertEquals($normalized_constraints[2]->getEndDate()->format('Y-m-d'), '2016-02-29');

    $this->assertEquals($normalized_constraints[3]->getStartDate()->format('Y-m-d'), '2016-04-02');
    $this->assertEquals($normalized_constraints[3]->getEndDate()->format('Y-m-d'), '2017-01-01');

    $this->assertEquals($normalized_constraints[4]->getStartDate()->format('Y-m-d'), '2016-01-01');
    $this->assertEquals($normalized_constraints[4]->getEndDate()->format('Y-m-d'), '2016-01-31');
  }

  public function testConstraintManager2() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-02-11 00:00');
    $ed = new \DateTime('2017-01-01 00:00');

    $sd1 = new \DateTime('2016-02-01 00:00');
    $ed1 = new \DateTime('2016-04-01 00:00');

    $sd2 = new \DateTime('2016-03-01 00:00');
    $ed2 = new \DateTime('2016-03-10 00:00');

    $constraints = array();
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 3, 0, $sd, $ed);
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 4, 0, $sd1, $ed1);
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 5, 0, $sd2, $ed2);

    $constraint_manager = new ConstraintManager($constraints);
    $normalized_constraints = $constraint_manager->normalizeConstraints('Roomify\Bat\Constraint\MinMaxDaysConstraint');

    $this->assertEquals(count($constraints), 3);
    $this->assertEquals(count($normalized_constraints), 4);

    $this->assertEquals($normalized_constraints[0]->getStartDate()->format('Y-m-d'), '2016-03-01');
    $this->assertEquals($normalized_constraints[0]->getEndDate()->format('Y-m-d'), '2016-03-10');

    $this->assertEquals($normalized_constraints[1]->getStartDate()->format('Y-m-d'), '2016-03-11');
    $this->assertEquals($normalized_constraints[1]->getEndDate()->format('Y-m-d'), '2016-04-01');

    $this->assertEquals($normalized_constraints[2]->getStartDate()->format('Y-m-d'), '2016-02-01');
    $this->assertEquals($normalized_constraints[2]->getEndDate()->format('Y-m-d'), '2016-02-29');

    $this->assertEquals($normalized_constraints[3]->getStartDate()->format('Y-m-d'), '2016-04-02');
    $this->assertEquals($normalized_constraints[3]->getEndDate()->format('Y-m-d'), '2017-01-01');
  }

  public function testConstraintManager3() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-05-01 00:00');
    $ed = new \DateTime('2017-01-01 00:00');

    $sd1 = new \DateTime('2016-02-01 00:00');
    $ed1 = new \DateTime('2016-03-01 00:00');

    $sd2 = new \DateTime('2016-03-10 00:00');
    $ed2 = new \DateTime('2016-03-20 00:00');

    $constraints = array();
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 3, 0, $sd, $ed);
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 4, 0, $sd1, $ed1);
    $constraints[] = new MinMaxDaysConstraint(array($u1, $u2), 5, 0, $sd2, $ed2);

    $constraint_manager = new ConstraintManager($constraints);
    $normalized_constraints = $constraint_manager->normalizeConstraints('Roomify\Bat\Constraint\MinMaxDaysConstraint');

    $this->assertEquals(count($constraints), 3);
    $this->assertEquals(count($normalized_constraints), 3);
  }

  public function testConstraintManager4() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-01-01 00:00');
    $ed = new \DateTime('2017-01-01 00:00');

    $sd1 = new \DateTime('2016-02-01 00:00');
    $ed1 = new \DateTime('2016-04-01 00:00');

    $sd2 = new \DateTime('2016-03-01 00:00');
    $ed2 = new \DateTime('2016-03-10 00:00');

    $constraints = array();
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 3, $sd, $ed);
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 4, $sd1, $ed1);
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 5, $sd2, $ed2);

    $constraint_manager = new ConstraintManager($constraints);
    $normalized_constraints = $constraint_manager->normalizeConstraints('Roomify\Bat\Constraint\CheckInDayConstraint');

    $this->assertEquals(count($constraints), 3);
    $this->assertEquals(count($normalized_constraints), 5);

    $this->assertEquals($normalized_constraints[0]->getCheckinDay(), '5');
    $this->assertEquals($normalized_constraints[1]->getCheckinDay(), '4');
    $this->assertEquals($normalized_constraints[2]->getCheckinDay(), '4');
    $this->assertEquals($normalized_constraints[3]->getCheckinDay(), '3');
    $this->assertEquals($normalized_constraints[4]->getCheckinDay(), '3');

    $this->assertEquals($normalized_constraints[0]->getStartDate()->format('Y-m-d'), '2016-03-01');
    $this->assertEquals($normalized_constraints[0]->getEndDate()->format('Y-m-d'), '2016-03-10');

    $this->assertEquals($normalized_constraints[1]->getStartDate()->format('Y-m-d'), '2016-03-11');
    $this->assertEquals($normalized_constraints[1]->getEndDate()->format('Y-m-d'), '2016-04-01');

    $this->assertEquals($normalized_constraints[2]->getStartDate()->format('Y-m-d'), '2016-02-01');
    $this->assertEquals($normalized_constraints[2]->getEndDate()->format('Y-m-d'), '2016-02-29');

    $this->assertEquals($normalized_constraints[3]->getStartDate()->format('Y-m-d'), '2016-04-02');
    $this->assertEquals($normalized_constraints[3]->getEndDate()->format('Y-m-d'), '2017-01-01');

    $this->assertEquals($normalized_constraints[4]->getStartDate()->format('Y-m-d'), '2016-01-01');
    $this->assertEquals($normalized_constraints[4]->getEndDate()->format('Y-m-d'), '2016-01-31');
  }

  public function testConstraintManager5() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-02-11 00:00');
    $ed = new \DateTime('2017-01-01 00:00');

    $sd1 = new \DateTime('2016-02-01 00:00');
    $ed1 = new \DateTime('2016-04-01 00:00');

    $sd2 = new \DateTime('2016-03-01 00:00');
    $ed2 = new \DateTime('2016-03-10 00:00');

    $constraints = array();
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 3, $sd, $ed);
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 4, $sd1, $ed1);
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 5, $sd2, $ed2);

    $constraint_manager = new ConstraintManager($constraints);
    $normalized_constraints = $constraint_manager->normalizeConstraints('Roomify\Bat\Constraint\CheckInDayConstraint');

    $this->assertEquals(count($constraints), 3);
    $this->assertEquals(count($normalized_constraints), 4);

    $this->assertEquals($normalized_constraints[0]->getCheckinDay(), '5');
    $this->assertEquals($normalized_constraints[1]->getCheckinDay(), '4');
    $this->assertEquals($normalized_constraints[2]->getCheckinDay(), '4');
    $this->assertEquals($normalized_constraints[3]->getCheckinDay(), '3');

    $this->assertEquals($normalized_constraints[0]->getStartDate()->format('Y-m-d'), '2016-03-01');
    $this->assertEquals($normalized_constraints[0]->getEndDate()->format('Y-m-d'), '2016-03-10');

    $this->assertEquals($normalized_constraints[1]->getStartDate()->format('Y-m-d'), '2016-03-11');
    $this->assertEquals($normalized_constraints[1]->getEndDate()->format('Y-m-d'), '2016-04-01');

    $this->assertEquals($normalized_constraints[2]->getStartDate()->format('Y-m-d'), '2016-02-01');
    $this->assertEquals($normalized_constraints[2]->getEndDate()->format('Y-m-d'), '2016-02-29');

    $this->assertEquals($normalized_constraints[3]->getStartDate()->format('Y-m-d'), '2016-04-02');
    $this->assertEquals($normalized_constraints[3]->getEndDate()->format('Y-m-d'), '2017-01-01');
  }

  public function testConstraintManager6() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-05-01 00:00');
    $ed = new \DateTime('2017-01-01 00:00');

    $sd1 = new \DateTime('2016-02-01 00:00');
    $ed1 = new \DateTime('2016-03-01 00:00');

    $sd2 = new \DateTime('2016-03-10 00:00');
    $ed2 = new \DateTime('2016-03-20 00:00');

    $constraints = array();
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 3, $sd, $ed);
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 4, $sd1, $ed1);
    $constraints[] = new CheckInDayConstraint(array($u1, $u2), 5, $sd2, $ed2);

    $constraint_manager = new ConstraintManager($constraints);
    $normalized_constraints = $constraint_manager->normalizeConstraints('Roomify\Bat\Constraint\CheckInDayConstraint');

    $this->assertEquals(count($constraints), 3);
    $this->assertEquals(count($normalized_constraints), 3);
  }

}
