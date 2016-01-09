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

  /**
   * @group failing
   */
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




}
