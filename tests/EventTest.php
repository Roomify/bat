<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;

use Roomify\Bat\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test Event.
   */
  public function testEvent() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-10 07:07');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit->getUnitId(), $event_state);

    $this->assertEquals($event->getUnitId(), 1);

    $this->assertEquals($event->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:12');
    $this->assertEquals($event->getEndDate()->format('Y-m-d H:i'), '2016-01-10 07:07');

    $this->assertEquals($event->startDay(), '1');
    $this->assertEquals($event->startMonth(), '1');
    $this->assertEquals($event->startYear(), '2016');
    $this->assertEquals($event->startWeek(), '53');
    $this->assertEquals($event->startHour(), '12');
    $this->assertEquals($event->startMinute(), '12');

    $this->assertEquals($event->endDay(), '10');
    $this->assertEquals($event->endMonth(), '1');
    $this->assertEquals($event->endYear(), '2016');
    $this->assertEquals($event->endWeek(), '01');
    $this->assertEquals($event->endHour(), '07');
    $this->assertEquals($event->endMinute(), '07');

    $this->assertEquals($event->isSameYear(), TRUE);
    $this->assertEquals($event->isSameMonth(), TRUE);
    $this->assertEquals($event->isSameDay(), FALSE);
    $this->assertEquals($event->isSameHour(), FALSE);

    $this->assertEquals($event->diff()->days, 8);

    $temp_date = new \DateTime('2016-01-05 10:10');
    $this->assertEquals($event->startsEarlier($temp_date), TRUE);
    $this->assertEquals($event->endsLater($temp_date), TRUE);

    $temp_date = new \DateTime('2015-12-05 10:10');
    $this->assertEquals($event->startsEarlier($temp_date), FALSE);
    $this->assertEquals($event->endsLater($temp_date), TRUE);

    $temp_date = new \DateTime('2016-02-05 10:10');
    $this->assertEquals($event->startsEarlier($temp_date), TRUE);
    $this->assertEquals($event->endsLater($temp_date), FALSE);

    $itemized = $event->itemizeEvent();

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
