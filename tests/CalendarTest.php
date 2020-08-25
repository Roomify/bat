<?php

namespace Roomify\Bat\Test;

use PHPUnit\Framework\TestCase;

use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Store\SqlDBStore;
use Roomify\Bat\Store\SqlLiteDBStore;

class CalendarTest extends TestCase {

  protected $pdo = NULL;

  public function setUp() {
    $pdo = NULL;
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

  public function testCalendarAddSingleEvent2UnitsSameHours() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,20,array());

    $units = array($u1,$u2);

    $sd1 = new \DateTime('2016-01-01 12:12');
    $sd2 = new \DateTime('2016-01-01 13:12');

    $e1 = new Event($sd1, $sd2, $u1, 11);
    $e2 = new Event($sd1, $sd2, $u2, 22);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $calendar->addEvents(array($e1, $e2), Event::BAT_HOURLY);

    $itemized = $calendar->getEventsItemized($sd1, $sd2);
    $normalized = $calendar->getEventsNormalized($sd1, $sd2, $itemized);

    //var_dump($normalized);
    //var_dump($itemized);

    // The day array for Unit 1 should have 1st of Jan as -1
    $this->assertEquals($itemized[1][Event::BAT_DAY]['2016']['1']['d1'], '-1');
    // The day array for Unit 2 should have 1st of Jan as -1
    $this->assertEquals($itemized[2][Event::BAT_DAY]['2016']['1']['d1'], '-1');

    // The hour array for Unit 1 and 2 for hours h12 and h13 should be -1
    $this->assertEquals($itemized[1][Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');
    $this->assertEquals($itemized[2][Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');

    $this->assertEquals($itemized[1][Event::BAT_HOUR]['2016']['1']['d1']['h13'], '-1');
    $this->assertEquals($itemized[2][Event::BAT_HOUR]['2016']['1']['d1']['h13'], '-1');

    // The minute array for each unit should have correct values for minutes involved in event

    // Test first hour
    for ($i = 12; $i<=59; $i++) {
      // First unit
      $this->assertEquals($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m'.$i], '11');
      // Second unit
      $this->assertEquals($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m'.$i], '22');
    }

    // Test second hour
    for ($i = 0; $i<=12; $i++) {
      // First unit
      if ($i <= 9) {
        $index = 'm0'.$i;
      }
      else {
        $index = 'm'.$i;
      }

      $this->assertEquals($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h13'][$index], '11');
      // Second unit
      $this->assertEquals($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h13'][$index], '22');
    }


    // For minutes outside of the event defined ones we should have the default values
    for ($i = 0; $i<=11; $i++) {
      // First unit
      if ($i <= 9) {
        $index = 'm0'.$i;
      }
      else {
        $index = 'm'.$i;
      }
      $this->assertEquals($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h12'][$index], '10');
      // Second unit
      $this->assertEquals($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h12'][$index], '20');
    }


    // For minutes outside of the event defined ones we should have the default values
    for ($i = 13; $i<=59; $i++) {
      $this->assertEquals($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h13']['m'.$i], '10');
      // Second unit
      $this->assertEquals($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h13']['m'.$i], '20');
    }

    // Finally check the normalized events

    // For Unit 1 we should have an event with the same start and end date and value as the event we put in
    $this->assertEquals($normalized[1][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[1][1]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 13:12');
    $this->assertEquals($normalized[1][1]->getValue(), 11);
    $this->assertEquals($normalized[1][1]->getUnitId(), 1);

    // Same for Unit 2
    $this->assertEquals($normalized[2][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[2][1]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 13:12');
    $this->assertEquals($normalized[2][1]->getValue(), 22);
    $this->assertEquals($normalized[2][1]->getUnitId(), 2);
  }

  public function testCalendarRetrieveWithEmptyDB() {
    $u1 = new Unit(1, 10, array());
    $u2 = new Unit(2, 20, array());

    $units = array($u1, $u2);

    $sd1 = new \DateTime('2016-01-01 12:12');
    $sd2 = new \DateTime('2016-01-01 13:12');

    $e1 = new Event($sd1, $sd2, $u1, 11);
    $e2 = new Event($sd1, $sd2, $u2, 22);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $itemized = $calendar->getEventsItemized($sd1, $sd2);
    $normalized = $calendar->getEventsNormalized($sd1, $sd2, $itemized);

    // The day array for Unit 1 should have 1st of Jan as -1
    $this->assertEquals($itemized[1][Event::BAT_DAY]['2016']['1']['d1'], '-1');
    // The day array for Unit 2 should have 1st of Jan as -1
    $this->assertEquals($itemized[2][Event::BAT_DAY]['2016']['1']['d1'], '-1');

    // The hour array for Unit 1 and 2 for hours h12 and h13 should be -1
    $this->assertEquals($itemized[1][Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');
    $this->assertEquals($itemized[2][Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');

    $this->assertEquals($itemized[1][Event::BAT_HOUR]['2016']['1']['d1']['h13'], '-1');
    $this->assertEquals($itemized[2][Event::BAT_HOUR]['2016']['1']['d1']['h13'], '-1');

    // The minute array for each unit should have correct values for minutes involved in event

    // Test first hour
    for ($i = 12; $i<=59; $i++) {
      // First unit
      $this->assertEquals($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m'.$i], '10');
      // Second unit
      $this->assertEquals($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m'.$i], '20');
    }

    // Test second hour
    for ($i = 0; $i<=12; $i++) {
      // First unit
      if ($i <= 9) {
        $index = 'm0'.$i;
      }
      else {
        $index = 'm'.$i;
      }

      $this->assertEquals($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h13'][$index], '10');
      // Second unit
      $this->assertEquals($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h13'][$index], '20');
    }


    // For minutes outside of the event defined ones the itemized event should be empty
    for ($i = 0; $i<=11; $i++) {
      // First unit
      if ($i <= 9) {
        $index = 'm0'.$i;
      }
      else {
        $index = 'm'.$i;
      }
      $this->assertEquals(isset($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h12'][$index]), FALSE);
      // Second unit
      $this->assertEquals(isset($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h12'][$index]), FALSE);
    }


    // For minutes outside of the event defined ones we should have the default values
    for ($i = 13; $i<=59; $i++) {
      $this->assertEquals(isset($itemized[1][Event::BAT_MINUTE]['2016']['1']['d1']['h13']['m'.$i]), FALSE);
      // Second unit
      $this->assertEquals(isset($itemized[2][Event::BAT_MINUTE]['2016']['1']['d1']['h13']['m'.$i]), FALSE);
    }

    // Finally check the normalized events

    // For Unit 1 we should have an event with the same start and end date and value as the event we put in
    $this->assertEquals($normalized[1][0]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[1][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 13:12');
    $this->assertEquals($normalized[1][0]->getValue(), 10);
    $this->assertEquals($normalized[1][0]->getUnitId(), 1);

    // Same for Unit 2
    $this->assertEquals($normalized[2][0]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[2][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 13:12');
    $this->assertEquals($normalized[2][0]->getValue(), 20);
    $this->assertEquals($normalized[2][0]->getUnitId(), 2);
  }

  public function testCalendarRetrieveEventSpanningYears() {
    $u1 = new Unit(1, 10, array());

    $units = array($u1);

    $sd1 = new \DateTime('2016-01-01 12:12');
    $sd2 = new \DateTime('2020-01-01 13:12');

    $e1 = new Event($sd1, $sd2, $u1, 11);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $calendar->addEvents(array($e1), Event::BAT_HOURLY);

    $itemized = $calendar->getEventsItemized($sd1, $sd2);
    $normalized = $calendar->getEventsNormalized($sd1, $sd2, $itemized);

    // For Unit 1 we should have an event with the same start and end date and value as the event we put in
    $this->assertEquals($normalized[1][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[1][1]->getEndDate()->format('Y-m-d H:i'), '2020-01-01 13:12');
    $this->assertEquals($normalized[1][1]->getValue(), 11);
    $this->assertEquals($normalized[1][1]->getUnitId(), 1);
  }

  public function testCalendarAddFullDayEvent() {
    $u1 = new Unit(1, 10, array());

    $units = array($u1);

    $sd1 = new \DateTime('2016-01-01 00:00');
    $sd2 = new \DateTime('2016-01-01 23:59');

    $e1 = new Event($sd1, $sd2, $u1, 11);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $calendar->addEvents(array($e1), Event::BAT_HOURLY);

    $itemized = $calendar->getEventsItemized($sd1, $sd2);
    $normalized = $calendar->getEventsNormalized($sd1, $sd2, $itemized);


    // For Unit 1 we should have an event with the same start and end date and value as the event we put in
    $this->assertEquals($normalized[1][0]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 00:00');
    $this->assertEquals($normalized[1][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 23:59');
    $this->assertEquals($normalized[1][0]->getValue(), 11);
    $this->assertEquals($normalized[1][0]->getUnitId(), 1);
  }

  public function testCalendarRetrievePeriodLargerThanEventsInDBDescribe() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,20,array());
    $u3 = new Unit(3,30,array());
    $u4 = new Unit(4,40,array());

    $units = array($u1, $u2, $u3, $u4);

    $d1 = new \DateTime('2015-12-31 10:00');//new \DateTime('2016-01-01 10:00');//

    $sd1 = new \DateTime('2016-01-01 12:12');
    $ed1 = new \DateTime('2016-01-01 13:12');

    $sd2 = new \DateTime('2016-01-01 13:12');
    $ed2 = new \DateTime('2016-01-02 15:29');

    $sd3 = new \DateTime('2016-02-01 12:00');
    $ed3 = new \DateTime('2016-02-10 14:56');

    $sd4 = new \DateTime('2016-03-02 23:59');
    $ed4 = new \DateTime('2016-03-15 00:00');

    $d2 = new \DateTime('2016-04-30 12:12');

    // Create 4 events for first unit
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e2u1 = new Event($sd2, $ed2, $u1, 111);
    $e3u1 = new Event($sd3, $ed3, $u1, 1111);
    $e4u1 = new Event($sd4, $ed4, $u1, 11111);

    // and a few more events
    $e5u2 = new Event($sd1, $ed2, $u2, 22);
    $e6u3 = new Event($sd3, $ed4, $u3, 33);
    // Make the last one longer than the end date of search
    $e7u4 = new Event($sd1, $ed4->add(new \DateInterval('P80D')), $u4, 44);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $calendar->addEvents(array($e1u1, $e2u1, $e3u1, $e4u1, $e5u2, $e6u3, $e7u4), Event::BAT_HOURLY);

    $itemized = $calendar->getEventsItemized($d1, $d2);
    $normalized = $calendar->getEventsNormalized($d1, $d2, $itemized);

    // Check results for Unit 1
    $this->assertEquals($normalized[1][0]->getStartDate()->format('Y-m-d H:i'), '2015-12-31 10:00');
    $this->assertEquals($normalized[1][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 12:11');
    $this->assertEquals($normalized[1][0]->getValue(), 10);
    $this->assertEquals($normalized[1][0]->getUnitId(), 1);

    $this->assertEquals($normalized[1][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[1][1]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 13:11');
    $this->assertEquals($normalized[1][1]->getValue(), 11);
    $this->assertEquals($normalized[1][1]->getUnitId(), 1);

    $this->assertEquals($normalized[1][2]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 13:12');
    $this->assertEquals($normalized[1][2]->getEndDate()->format('Y-m-d H:i'), '2016-01-02 15:29');
    $this->assertEquals($normalized[1][2]->getValue(), 111);
    $this->assertEquals($normalized[1][2]->getUnitId(), 1);

    $this->assertEquals($normalized[1][3]->getStartDate()->format('Y-m-d H:i'), '2016-01-02 15:30');
    $this->assertEquals($normalized[1][3]->getEndDate()->format('Y-m-d H:i'), '2016-02-01 11:59');
    $this->assertEquals($normalized[1][3]->getValue(), 10);
    $this->assertEquals($normalized[1][3]->getUnitId(), 1);

    $this->assertEquals($normalized[1][4]->getStartDate()->format('Y-m-d H:i'), '2016-02-01 12:00');
    $this->assertEquals($normalized[1][4]->getEndDate()->format('Y-m-d H:i'), '2016-02-10 14:56');
    $this->assertEquals($normalized[1][4]->getValue(), 1111);
    $this->assertEquals($normalized[1][4]->getUnitId(), 1);

    $this->assertEquals($normalized[1][5]->getStartDate()->format('Y-m-d H:i'), '2016-02-10 14:57');
    $this->assertEquals($normalized[1][5]->getEndDate()->format('Y-m-d H:i'), '2016-03-02 23:58');
    $this->assertEquals($normalized[1][5]->getValue(), 10);
    $this->assertEquals($normalized[1][5]->getUnitId(), 1);

    $this->assertEquals($normalized[1][6]->getStartDate()->format('Y-m-d H:i'), '2016-03-02 23:59');
    $this->assertEquals($normalized[1][6]->getEndDate()->format('Y-m-d H:i'), '2016-03-15 00:00');
    $this->assertEquals($normalized[1][6]->getValue(), 11111);
    $this->assertEquals($normalized[1][6]->getUnitId(), 1);

    $this->assertEquals($normalized[1][7]->getStartDate()->format('Y-m-d H:i'), '2016-03-15 00:01');
    $this->assertEquals($normalized[1][7]->getEndDate()->format('Y-m-d H:i'), '2016-04-30 12:12');
    $this->assertEquals($normalized[1][7]->getValue(), 10);
    $this->assertEquals($normalized[1][7]->getUnitId(), 1);

    // Check results for Unit 2
    $this->assertEquals($normalized[2][0]->getStartDate()->format('Y-m-d H:i'), '2015-12-31 10:00');
    $this->assertEquals($normalized[2][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 12:11');
    $this->assertEquals($normalized[2][0]->getValue(), 20);
    $this->assertEquals($normalized[2][0]->getUnitId(), 2);

    $this->assertEquals($normalized[2][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[2][1]->getEndDate()->format('Y-m-d H:i'), '2016-01-02 15:29');
    $this->assertEquals($normalized[2][1]->getValue(), 22);
    $this->assertEquals($normalized[2][1]->getUnitId(), 2);

    $this->assertEquals($normalized[2][2]->getStartDate()->format('Y-m-d H:i'), '2016-01-02 15:30');
    $this->assertEquals($normalized[2][2]->getEndDate()->format('Y-m-d H:i'), '2016-04-30 12:12');
    $this->assertEquals($normalized[2][2]->getValue(), 20);
    $this->assertEquals($normalized[2][2]->getUnitId(), 2);

    // Check results for Unit 3
    $this->assertEquals($normalized[3][0]->getStartDate()->format('Y-m-d H:i'), '2015-12-31 10:00');
    $this->assertEquals($normalized[3][0]->getEndDate()->format('Y-m-d H:i'), '2016-02-01 11:59');
    $this->assertEquals($normalized[3][0]->getValue(), 30);
    $this->assertEquals($normalized[3][0]->getUnitId(), 3);

    $this->assertEquals($normalized[3][1]->getStartDate()->format('Y-m-d H:i'), '2016-02-01 12:00');
    $this->assertEquals($normalized[3][1]->getEndDate()->format('Y-m-d H:i'), '2016-03-15 00:00');
    $this->assertEquals($normalized[3][1]->getValue(), 33);
    $this->assertEquals($normalized[3][1]->getUnitId(), 3);

    $this->assertEquals($normalized[3][2]->getStartDate()->format('Y-m-d H:i'), '2016-03-15 00:01');
    $this->assertEquals($normalized[3][2]->getEndDate()->format('Y-m-d H:i'), '2016-04-30 12:12');
    $this->assertEquals($normalized[3][2]->getValue(), 30);
    $this->assertEquals($normalized[3][2]->getUnitId(), 3);

    // Check results for Unit 4
    $this->assertEquals($normalized[4][0]->getStartDate()->format('Y-m-d H:i'), '2015-12-31 10:00');
    $this->assertEquals($normalized[4][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-01 12:11');
    $this->assertEquals($normalized[4][0]->getValue(), 40);
    $this->assertEquals($normalized[4][0]->getUnitId(), 4);

    $this->assertEquals($normalized[4][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($normalized[4][1]->getEndDate()->format('Y-m-d H:i'), '2016-04-30 12:12');
    $this->assertEquals($normalized[4][1]->getValue(), 44);
    $this->assertEquals($normalized[4][1]->getUnitId(), 4);
  }

  public function testCalendarGetMatchingUnitsWithValidStates() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-01-01 12:12');
    $ed = new \DateTime('2016-03-31 18:12');

    $sd1 = new \DateTime('2016-01-02 12:12');
    $ed1 = new \DateTime('2016-01-10 13:12');

    $sd2 = new \DateTime('2016-01-16 13:12');
    $ed2 = new \DateTime('2016-01-20 15:29');

    $sd3 = new \DateTime('2016-01-28 13:12');
    $ed3 = new \DateTime('2016-02-03 15:29');

    $sd4 = new \DateTime('2016-02-03 18:08');
    $ed4 = new \DateTime('2016-02-03 21:29');

    // Create some event for unit 1 and 2
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 13);
    $e2u1 = new Event($sd2, $ed2, $u1, 11);
    $e3u1 = new Event($sd4, $ed4, $u1, 11);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1, $e2u1, $e3u1, $e1u2), Event::BAT_HOURLY);

    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 17), array());

    $valid_unit_ids = array_keys($response->getIncluded());

    // The result should be the unit 1.
    $this->assertEquals($valid_unit_ids[0], 1);
  }

  public function testCalendarGetMatchingUnitsWithInvalidStates() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());
    $u3 = new Unit(3,10,array());

    $units = array($u1, $u2, $u3);

    $sd = new \DateTime('2016-01-01 15:10');
    $ed = new \DateTime('2016-06-30 18:00');

    $sd1 = new \DateTime('2016-01-07 02:12');
    $ed1 = new \DateTime('2016-01-13 13:12');

    $sd2 = new \DateTime('2016-01-13 13:14');
    $ed2 = new \DateTime('2016-01-20 15:29');

    $sd3 = new \DateTime('2016-01-31 13:12');
    $ed3 = new \DateTime('2016-02-05 15:41');

    $sd4 = new \DateTime('2016-02-11 18:08');
    $ed4 = new \DateTime('2016-02-28 22:15');

    // Create some event for units 1,2,3
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 13);
    $e1u3 = new Event($sd1, $ed1, $u3, 15);
    $e2u1 = new Event($sd2, $ed2, $u1, 15);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1, $e1u2, $e1u3, $e2u1), Event::BAT_HOURLY);

    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 13), array());

    $valid_unit_ids = array_keys($response->getIncluded());
    $invalid_unit_ids = array_keys($response->getExcluded());

    // Check valid Units.
    $this->assertEquals($valid_unit_ids[0], 2);
    // Check invalid states.
    $this->assertEquals($invalid_unit_ids[0], 1);
    $this->assertEquals($invalid_unit_ids[1], 3);

    // Try to change valid states.
    $response_2 = $calendar->getMatchingUnits($sd, $ed, array(10, 15, 11), array());

    $valid_unit_ids_2 = array_keys($response_2->getIncluded());
    $invalid_unit_ids_2 = array_keys($response_2->getExcluded());

    // Check valid Units.
    $this->assertEquals($valid_unit_ids_2[0], 1);
    $this->assertEquals($valid_unit_ids_2[1], 3);
    // Check invalid states.
    $this->assertEquals($invalid_unit_ids_2[0], 2);

    // Try to change dates.
    $response_3 = $calendar->getMatchingUnits($sd1, $ed1, array(10, 11, 15), array());
    $valid_unit_ids_3 = array_keys($response_3->getIncluded());
    $invalid_unit_ids_3 = array_keys($response_3->getExcluded());

    // Check valid Units.
    $this->assertEquals($valid_unit_ids_3[0], 1);
    $this->assertEquals($valid_unit_ids_3[1], 3);
    // Check invalid states.
    $this->assertEquals($invalid_unit_ids_3[0], 2);
  }

  public function testCalendarGetMatchingUnitsIntersect() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());
    $u3 = new Unit(3,10,array());

    $units = array($u1, $u2, $u3);

    $sd = new \DateTime('2016-01-01 15:10');
    $ed = new \DateTime('2016-06-30 18:00');

    $sd1 = new \DateTime('2016-01-07 02:12');
    $ed1 = new \DateTime('2016-01-13 13:12');

    $sd2 = new \DateTime('2016-01-13 13:14');
    $ed2 = new \DateTime('2016-01-20 15:29');

    $sd3 = new \DateTime('2016-01-31 13:12');
    $ed3 = new \DateTime('2016-02-05 15:41');

    $sd4 = new \DateTime('2016-02-11 18:08');
    $ed4 = new \DateTime('2016-02-28 22:15');

    // Create some event for units 1,2,3
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 13);
    $e1u3 = new Event($sd1, $ed1, $u3, 15);
    $e2u1 = new Event($sd2, $ed2, $u1, 15);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1, $e1u2, $e1u3, $e2u1), Event::BAT_HOURLY);

    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 13), array(), TRUE);
    $this->assertEquals(count($response->getIncluded()), 3);

    $response = $calendar->getMatchingUnits($sd, $ed, array(15), array(), TRUE);
    $this->assertEquals(count($response->getIncluded()), 2);

    $response = $calendar->getMatchingUnits($sd, $ed, array(13), array(), TRUE);
    $this->assertEquals(count($response->getIncluded()), 1);
  }

  public function testCalendarCalendarResponseFunctions() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());
    $u3 = new Unit(3,10,array());

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

    $this->assertEquals($response->getStartDate()->format('Y-m-d H:i'), '2016-01-01 15:10');
    $this->assertEquals($response->getEndDate()->format('Y-m-d H:i'), '2016-06-30 18:00');

    $valid_unit_ids = array_keys($response->getIncluded());
    $invalid_unit_ids = array_keys($response->getExcluded());

    // Remove the unit 2 from valid unit ids.
    $response->removeFromMatched($u2, $reason = 'Just for testing.');
    $valid_unit_ids = array_keys($response->getIncluded());
    $invalid_unit_ids = array_keys($response->getExcluded());

    // Now the unit 2 should be invalid.
    $this->assertEquals($invalid_unit_ids[0], 2);

    // Try to remove an a nonexistent unit from response.
    $response->removeFromMatched($u3, $reason = 'Just for testing.');
  }

  public function testCalendarHourlyEventFullDayRange() {
    $u1 = new Unit(1,10,array());

    $sd = new \DateTime('2016-01-18 12:21');
    $ed = new \DateTime('2016-01-18 14:20');

    $e = new Event($sd, $ed, $u1, 5);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar(array($u1), $store);

    // Add the events.
    $calendar->addEvents(array($e), Event::BAT_HOURLY);

    $events = $calendar->getEvents(new \DateTime('2016-01-18 00:00'), new \DateTime('2016-01-19 00:00'));

    // We should get back three events
    $this->assertEquals($events[1][0]->getStartDate()->format('Y-m-d H:i'), '2016-01-18 00:00');
    $this->assertEquals($events[1][0]->getEndDate()->format('Y-m-d H:i'), '2016-01-18 12:20');
    $this->assertEquals($events[1][0]->getValue(), 10);

    $this->assertEquals($events[1][1]->getStartDate()->format('Y-m-d H:i'), '2016-01-18 12:21');
    $this->assertEquals($events[1][1]->getEndDate()->format('Y-m-d H:i'), '2016-01-18 14:20');
    $this->assertEquals($events[1][1]->getValue(), 5);

    $this->assertEquals($events[1][2]->getStartDate()->format('Y-m-d H:i'), '2016-01-18 14:21');
    $this->assertEquals($events[1][2]->getEndDate()->format('Y-m-d H:i'), '2016-01-19 00:00');
    $this->assertEquals($events[1][2]->getValue(), 10);
  }

  public function testCalendarLastDayOfMonth() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,20,array());

    $units = array($u1,$u2);

    $sd1 = new \DateTime('2016-03-31 12:12');
    $sd2 = new \DateTime('2016-03-31 13:12');

    $e1 = new Event($sd1, $sd2, $u1, 11);
    $e2 = new Event($sd1, $sd2, $u2, 22);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $calendar->addEvents(array($e1, $e2), Event::BAT_HOURLY);

    $this->assertEquals($calendar->getStates($sd1, $sd2), array(1 => array(11 => 11), 2 => array(22 => 22)));
  }

  public function testSavedItemizedEvents() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());

    $units = array($u1, $u2);

    $sd = new \DateTime('2016-01-01 12:12');
    $ed = new \DateTime('2016-03-31 18:12');

    $sd1 = new \DateTime('2016-01-02 12:12');
    $ed1 = new \DateTime('2016-01-10 13:12');

    $sd2 = new \DateTime('2016-01-16 13:12');
    $ed2 = new \DateTime('2016-01-20 15:29');

    $sd3 = new \DateTime('2016-01-28 13:12');
    $ed3 = new \DateTime('2016-02-03 15:29');

    $sd4 = new \DateTime('2016-02-03 18:08');
    $ed4 = new \DateTime('2016-02-03 21:29');

    $sd_wide = new \DateTime('2015-01-01 12:12');
    $ed_wide = new \DateTime('2017-01-01 12:12');

    // Create some event for unit 1 and 2
    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 13);
    $e2u1 = new Event($sd2, $ed2, $u1, 11);
    $e3u1 = new Event($sd4, $ed4, $u1, 11);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    // Add the events.
    $calendar->addEvents(array($e1u1, $e2u1, $e3u1, $e1u2), Event::BAT_HOURLY);

    $response = $calendar->getMatchingUnits($sd_wide, $ed_wide, array(10, 11, 17), array(), FALSE, FALSE);
    $valid_unit_ids = array_keys($response->getIncluded());
    // The result should be the unit 1.
    $this->assertEquals($valid_unit_ids[0], 1);


    $response = $calendar->getMatchingUnits($sd, $ed, array(10, 11, 17), array(), FALSE, FALSE);
    $valid_unit_ids = array_keys($response->getIncluded());
    // The result should be the unit 1.
    $this->assertEquals($valid_unit_ids[0], 1);
  }

  public function testGetMatchingUnitsWithoutReset() {
    $u1 = new Unit(1,10,array());
    $u2 = new Unit(2,10,array());
    $u3 = new Unit(3,10,array());
    $u4 = new Unit(4,10,array());

    $units = array($u1, $u2, $u3, $u4);

    $sd = new \DateTime('2016-05-01 00:00');
    $ed = new \DateTime('2016-05-01 23:59');

    $sd1 = new \DateTime('2016-05-01 10:00');
    $ed1 = new \DateTime('2016-05-01 10:59');

    $sd2 = new \DateTime('2016-05-01 14:00');
    $ed2 = new \DateTime('2016-05-01 14:29');

    $sd3 = new \DateTime('2016-05-01 09:45');
    $ed3 = new \DateTime('2016-05-01 10:14');

    $sd4 = new \DateTime('2016-05-01 10:45');
    $ed4 = new \DateTime('2016-05-01 10:59');

    $e1u1 = new Event($sd1, $ed1, $u1, 11);
    $e1u2 = new Event($sd1, $ed1, $u2, 11);
    $e1u3 = new Event($sd1, $ed1, $u3, 11);

    $e2u1 = new Event($sd2, $ed2, $u1, 11);
    $e2u2 = new Event($sd2, $ed2, $u2, 11);
    $e2u3 = new Event($sd2, $ed2, $u3, 11);
    $e2u4 = new Event($sd2, $ed2, $u3, 11);

    $e3u4 = new Event($sd3, $ed3, $u4, 11);
    $e4u4 = new Event($sd4, $ed4, $u4, 11);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $calendar->addEvents(array($e1u1, $e1u2, $e1u3, $e2u1, $e2u2, $e2u3, $e2u4, $e3u4, $e4u4), Event::BAT_HOURLY);

    $event_ids = $calendar->getEvents($sd, $ed);

    foreach ($event_ids as $unit_id => $unit_events) {
      foreach ($unit_events as $key => $event) {
        $event_start_date = $event->getStartDate();
        $dates[$event_start_date->getTimestamp()] = $event_start_date;
      }
    }

    ksort($dates);
    $dates = array_values($dates);

    for ($i = 0; $i < (count($dates) - 1); $i++) {
      $sd = $dates[$i];
      $ed = clone($dates[$i + 1]);
      $ed->sub(new \DateInterval('PT1M'));

      $response = $calendar->getMatchingUnits($sd, $ed, array(10), array(), FALSE, FALSE);

      if (count(array_keys($response->getIncluded()))) {
        if ($i == 0) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 00:00');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 09:44');
        }
        elseif ($i == 1) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 09:45');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 09:59');
        }
        elseif ($i == 3) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 10:15');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 10:44');
        }
        elseif ($i == 5) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 11:00');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 13:59');
        }
        elseif ($i == 7) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 14:30');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 23:59');
        }
      }
      else {
        if ($i == 2) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 10:00');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 10:14');
        }
        elseif ($i == 4) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 10:45');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 10:59');
        }
        elseif ($i == 6) {
          $this->assertEquals($sd->format('Y-m-d H:i'), '2016-05-01 14:00');
          $this->assertEquals($ed->format('Y-m-d H:i'), '2016-05-01 14:29');
        }
      }
    }
  }

  public function testSplitHour() {
    $u1 = new Unit(1,0,array());

    $units = array($u1);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $sd1 = new \DateTime('2016-08-01 10:00');
    $ed1 = new \DateTime('2016-08-01 10:59');

    $e1s11 = new Event($sd1, $ed1, $u1, 11);

    $calendar->addEvents(array($e1s11), Event::BAT_HOURLY);

    $itemized = $calendar->getEventsItemized($sd1, $ed1);

    $this->assertEquals($itemized['1']['bat_day']['2016']['8']['d1'], '-1');
    $this->assertEquals($itemized['1']['bat_hour']['2016']['8']['d1']['h10'], '11');

    $sd2 = new \DateTime('2016-08-01 10:00');
    $ed2 = new \DateTime('2016-08-01 10:14');

    $e2s12 = new Event($sd2, $ed2, $u1, 12);

    $calendar->addEvents(array($e2s12), Event::BAT_HOURLY);

    $itemized = $calendar->getEventsItemized($sd1, $ed1);

    $this->assertEquals($itemized['1'][Event::BAT_DAY]['2016']['8']['d1'], '-1');
    $this->assertEquals($itemized['1'][Event::BAT_HOUR]['2016']['8']['d1']['h10'], '-1');
    $this->assertEquals($itemized['1'][Event::BAT_MINUTE]['2016']['8']['d1']['h10']['m10'], '12');
    $this->assertEquals($itemized['1'][Event::BAT_MINUTE]['2016']['8']['d1']['h10']['m14'], '12');
    $this->assertEquals($itemized['1'][Event::BAT_MINUTE]['2016']['8']['d1']['h10']['m15'], '11');
    $this->assertEquals($itemized['1'][Event::BAT_MINUTE]['2016']['8']['d1']['h10']['m59'], '11');
  }

  public function testDstTransition1() {
    $u1 = new Unit(1,0,array());

    $units = array($u1);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $sd1 = new \DateTime('2017-03-26 00:57');
    $ed1 = new \DateTime('2017-03-26 03:03');

    $e1s11 = new Event($sd1, $ed1, $u1, 11);

    $calendar->addEvents(array($e1s11), Event::BAT_HOURLY);

    $events = $calendar->getEvents($sd1, $ed1);

    $this->assertEquals($events[1][1]->getStartDate()->format('Y-m-d H:i'), '2017-03-26 00:57');
    $this->assertEquals($events[1][1]->getEndDate()->format('Y-m-d H:i'), '2017-03-26 03:03');
    $this->assertEquals($events[1][1]->getValue(), 11);
  }

  public function testDstTransition2() {
    $u1 = new Unit(1,0,array());

    $units = array($u1);

    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);

    $calendar = new Calendar($units, $store);

    $sd1 = new \DateTime('2017-10-29 00:57');
    $ed1 = new \DateTime('2017-10-29 04:03');

    $e1s11 = new Event($sd1, $ed1, $u1, 11);

    $calendar->addEvents(array($e1s11), Event::BAT_HOURLY);

    $events = $calendar->getEvents($sd1, $ed1);

    $this->assertEquals($events[1][1]->getStartDate()->format('Y-m-d H:i'), '2017-10-29 00:57');
    $this->assertEquals($events[1][1]->getEndDate()->format('Y-m-d H:i'), '2017-10-29 04:03');
    $this->assertEquals($events[1][1]->getValue(), 11);
  }

  public function testPartialOverlap() {
    $u1 = new Unit(1, 0, array());
    $units = array($u1);
    $store = new SqlLiteDBStore($this->pdo, 'availability_event', SqlDBStore::BAT_STATE);
    $calendar = new Calendar($units, $store);

    $sd = new \DateTime('2020-07-12 00:00');
    $ed = new \DateTime('2020-07-13 23:59');
    // Base event to be split.
    $sd1 = new \DateTime('2020-07-12 00:00');
    $ed1 = new \DateTime('2020-07-13 23:59');
    $e1s11 = new Event($sd1, $ed1, $u1, 11);
    // Splits an existing day.
    $sd2 = new \DateTime('2020-07-12 01:00');
    $ed2 = new \DateTime('2020-07-12 03:00');
    $e2s22 = new Event($sd2, $ed2, $u1, 22);
    // Splits an existing hour when day is already split.
    $sd3 = new \DateTime('2020-07-12 04:20');
    $ed3 = new \DateTime('2020-07-12 04:25');
    $e3s33 = new Event($sd3, $ed3, $u1, 33);
    // Splits an hour in an existing day not previously split.
    $sd4 = new \DateTime('2020-07-13 04:20');
    $ed4 = new \DateTime('2020-07-13 04:25');
    $e4s44 = new Event($sd4, $ed4, $u1, 44);

    $calendar->addEvents(array($e1s11, $e2s22, $e3s33, $e4s44), Event::BAT_HOURLY);
    $events = $calendar->getEvents($sd, $ed);

    $this->assertEquals(11, $events[1][0]->getValue());
    $this->assertEquals(22, $events[1][1]->getValue());
    $this->assertEquals(11, $events[1][2]->getValue());
    $this->assertEquals(33, $events[1][3]->getValue());
    $this->assertEquals(11, $events[1][4]->getValue());
    $this->assertEquals(44, $events[1][5]->getValue());
    $this->assertEquals(11, $events[1][4]->getValue());
  }

}
