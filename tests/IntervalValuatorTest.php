<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Store\Store;
use Roomify\Bat\Store\SqlLiteDBStore;
use Roomify\Bat\Unit\Unit;
use Roomify\Bat\Event\Event;
use Roomify\Bat\Calendar\Calendar;
use Roomify\Bat\Valuator\IntervalValuator;

use Roomify\Bat\Test\SetupStore;


class AggregateValuatorTest extends \PHPUnit_Framework_TestCase {

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

    $this->e1 = new Event($sd1, $ed1, $this->u1->getUnitId(), $es1);
    $this->e2 = new Event ($sd2, $ed2, $this->u1->getUnitId(), $es2);

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

    $itemized = $calendar->getEventsItemized(new \DateTime('2016-01-01 00:00'), new \DateTime('2016-01-11 23:59'));

    $valuator = new IntervalValuator(new \DateTime('2016-01-01 00:00'),new \DateTime('2016-01-11 23:59'), $this->u1, $store, new \DateInterval('P1D'));
    $valuator->determineValue();




    // Create a mock store
    /*$store = $this->getMockBuilder('Roomify\Bat\Store\SqlLiteDBStore')
      ->disableOriginalConstructor()
      ->getMock();

    print_r(get_class_methods($store));

    $store->method('getEventData')
      ->willReturn('foo');

    var_dump($store->getEventData(new \DateTime('2016-01-01 12:12'),new \DateTime('2016-01-10 07:07'), array() ));
    var_dump($store);*/
  }
}
