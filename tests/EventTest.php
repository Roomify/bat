<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;

use Roomify\Bat\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase {

  private $event;

  public function setUp() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-10 07:07');
    $unit = new Unit(1, 2, array());

    $this->event = new Event($start_date, $end_date, $unit->getUnitId(), $event_state);
  }

  public function testEventGetUnitId() {
    $this->assertEquals($this->event->getUnitId(), 1);
  }

  public function testEventGetStartDate() {
    $this->assertEquals($this->event->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
  }

  public function testEventGetEndDate() {
    $this->assertEquals($this->event->getEndDate()->format('Y-m-d H:i'), '2016-01-10 07:07');
  }

  public function testEventStartDay() {
    $this->assertEquals($this->event->startDay(), '1');
  }

  public function testEventStartMonth() {
    $this->assertEquals($this->event->startMonth(), '1');
  }

  public function testEventStartYear() {
    $this->assertEquals($this->event->startYear(), '2016');
  }

  public function testEventStartWeek() {
    $this->assertEquals($this->event->startWeek(), '53');
  }

  public function testEventStartHour() {
    $this->assertEquals($this->event->startHour(), '12');
  }

  public function testEventStartMinute() {
    $this->assertEquals($this->event->startMinute(), '12');
  }

  public function testEventEndDay() {
    $this->assertEquals($this->event->endDay(), '10');
  }

  public function testEventEndMonth() {
    $this->assertEquals($this->event->endMonth(), '1');
  }

  public function testEventEndYear() {
    $this->assertEquals($this->event->endYear(), '2016');
  }

  public function testEventEndWeek() {
    $this->assertEquals($this->event->endWeek(), '01');
  }

  public function testEventEndHour() {
    $this->assertEquals($this->event->endHour(), '07');
  }

  public function testEventEndMinute() {
    $this->assertEquals($this->event->endMinute(), '07');
  }

  public function testEventIsSameYear() {
    $this->assertEquals($this->event->isSameYear(), TRUE);
  }

  public function testEventIsSameMonth() {
    $this->assertEquals($this->event->isSameMonth(), TRUE);
  }

  public function testEventIsSameDay() {
    $this->assertEquals($this->event->isSameDay(), FALSE);
  }

  public function testEventIsSameHour() {
    $this->assertEquals($this->event->isSameHour(), FALSE);
  }

  public function testEventDateDiff() {
    $this->assertEquals($this->event->diff()->days, 8);
  }

  public function testEventStartsEarlier() {
    $temp_date = new \DateTime('2016-01-05 10:10');
    $this->assertEquals($this->event->startsEarlier($temp_date), TRUE);

    $temp_date = new \DateTime('2015-12-05 10:10');
    $this->assertEquals($this->event->startsEarlier($temp_date), FALSE);

    $temp_date = new \DateTime('2016-02-05 10:10');
    $this->assertEquals($this->event->startsEarlier($temp_date), TRUE);
  }

  public function testEventEndsLater() {
    $temp_date = new \DateTime('2016-01-05 10:10');
    $this->assertEquals($this->event->endsLater($temp_date), TRUE);

    $temp_date = new \DateTime('2015-12-05 10:10');
    $this->assertEquals($this->event->endsLater($temp_date), TRUE);

    $temp_date = new \DateTime('2016-02-05 10:10');
    $this->assertEquals($this->event->endsLater($temp_date), FALSE);
  }

  public function testEventItemizeEvent() {
    $itemized = $this->event->itemizeEvent();

    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d1'], '-1');
    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d10'], '-1');

    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h13'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h14'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h15'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h16'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h17'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h18'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h19'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h20'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h21'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h22'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h23'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h0'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h1'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h2'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h3'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h4'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h5'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h6'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d10']['h7'], '-1');

    for ($i = 12; $i <= 59; $i++) {
      if ($i <= 9) {
        $index = 'm0' . $i;
      }
      else {
        $index = 'm' . $i;
      }
      $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['1']['d1']['h12'][$index], '5');
    }
    for ($i = 0; $i <= 7; $i++) {
      if ($i <= 9) {
        $index = 'm0' . $i;
      }
      else {
        $index = 'm' . $i;
      }
      $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['1']['d10']['h7'][$index], '5');
    }
  }

}
