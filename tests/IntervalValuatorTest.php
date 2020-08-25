<?php

namespace Roomify\Bat\Test;

use PHPUnit\Framework\TestCase;

use Roomify\Bat\Store\Store;
use Roomify\Bat\Store\SqlLiteDBStore;
use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Valuator\IntervalValuator;

use Roomify\Bat\Test\SetupStore;

class IntervalValuatorTest extends TestCase {

  protected $e1;
  protected $e2;
  protected $e3;
  protected $u1;
  protected $u2;
  protected $u3;

  protected $pdo = NULL;

  public function setUp() {
    $es1 = 5;
    $sd1 = new \DateTime('2016-01-01 00:00');
    $ed1 = new \DateTime('2016-01-10 23:59');

    $es2 = 3;
    $sd2 = new \DateTime('2016-01-11 00:00');
    $ed2 = new \DateTime('2016-01-12 23:59');

    $this->u1 = new Unit(1, 2, array());
    $this->u2 = new Unit(2, 4, array());
    $this->u3 = new Unit(3, 6, array());

    $this->e1 = new Event($sd1, $ed1, $this->u1, $es1);
    $this->e2 = new Event ($sd2, $ed2, $this->u1, $es2);

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

  public function testAggregateValue() {
    $store = new SqlLiteDBStore($this->pdo, 'availability_event');

    $calendar = new Calendar(array($this->u1, $this->u2, $this->u3), $store);

    $calendar->addEvents(array($this->e1, $this->e2), Event::BAT_HOURLY);

    // Interval: 11 days
    // Duration: 1 day
    // Value: 5 * 10 + 3 * 1
    $valuator = new IntervalValuator(new \DateTime('2016-01-01 00:00'), new \DateTime('2016-01-11 23:59'), $this->u1, $store, new \DateInterval('P1D'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 53);

    // Interval: 2 days
    // Duration: 1 day
    // Value: 3 * 2
    $valuator = new IntervalValuator(new \DateTime('2016-01-11 00:00'), new \DateTime('2016-01-12 23:59'), $this->u1, $store, new \DateInterval('P1D'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 6);

    // Interval: 6 day
    // Duration: 1 day
    // Value: 5 * 5 + 3 * 1
    $valuator = new IntervalValuator(new \DateTime('2016-01-06 00:00'), new \DateTime('2016-01-11 23:59'), $this->u1, $store, new \DateInterval('P1D'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 28);

    // Interval: 2 hours
    // Duration: 15 minutes
    // Value: 5 * 8
    $valuator = new IntervalValuator(new \DateTime('2016-01-01 11:00'), new \DateTime('2016-01-01 12:59'), $this->u1, $store, new \DateInterval('PT15M'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 40);

    // Interval: 2 hours
    // Duration: 15 minutes
    // Value: 3 * 8
    $valuator = new IntervalValuator(new \DateTime('2016-01-11 11:00'), new \DateTime('2016-01-11 12:59'), $this->u1, $store, new \DateInterval('PT15M'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 24);

    // Interval: 2 hours
    // Duration: 36 minutes
    // Value: 3 * 3.333
    $valuator = new IntervalValuator(new \DateTime('2016-01-11 11:00'), new \DateTime('2016-01-11 12:59'), $this->u1, $store, new \DateInterval('PT36M'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 10);

    // Interval: 15 hours
    // Duration: 3 hours
    // Value: 5 * 1 + 3 * 4
    $valuator = new IntervalValuator(new \DateTime('2016-01-10 21:00'), new \DateTime('2016-01-11 11:59'), $this->u1, $store, new \DateInterval('PT3H'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 17);

    // Interval: 15 hours
    // Duration: 3 hours
    // Value: 5 * 2/3 + 3 * 4.333
    $valuator = new IntervalValuator(new \DateTime('2016-01-10 22:00'), new \DateTime('2016-01-11 12:59'), $this->u1, $store, new \DateInterval('PT3H'));
    $value = $valuator->determineValue();
    $this->assertEquals($value, 16.33);
  }

}
