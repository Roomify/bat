<?php

namespace Roomify\Bat\Test;

use PHPUnit\Framework\TestCase;

use Roomify\Bat\Unit\Unit;

use Roomify\Bat\Event\Event;
use Roomify\Bat\Event\EventItemizer;

class EventTest extends TestCase {

  private $event;

  public function setUp() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-10 07:07');
    $unit = new Unit(1, 2, array());

    $this->event = new Event($start_date, $end_date, $unit, $event_state);
  }

  public function testEventUnitId() {
    $this->event->setUnitId(5);
    $this->assertEquals($this->event->getUnitId(), 5);
  }

  public function testEventValue() {
    $this->event->setValue(15);
    $this->assertEquals($this->event->getValue(), 15);
  }

  public function testEventStartDate() {
    $this->event->setStartDate(new \DateTime('2016-01-01 12:13'));
    $this->assertEquals($this->event->getStartDate()->format('Y-m-d H:i'), '2016-01-01 12:13');
  }

  public function testEventEndDate() {
    $this->event->setEndDate(new \DateTime('2016-01-11 07:07'));
    $this->assertEquals($this->event->getEndDate()->format('Y-m-d H:i'), '2016-01-11 07:07');
  }

  public function testtDateToString() {
    $sd = $this->event->startDateToString();
    $this->assertEquals($this->event->getStartDate()->format('Y-m-d H:i'), $sd);

    $ed = $this->event->endDateToString();
    $this->assertEquals($this->event->getEndDate()->format('Y-m-d H:i'), $ed);

  }

  public function testEndMonthDate() {
    $end_month = $this->event->endMonthDate(new \DateTime('2016-01-01 12:13'));
    $this->assertEquals($end_month->format('Y-m-d H:i'), '2016-01-31 23:59');

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

    $this->event->setEndDate(new \DateTime('2017-01-10 07:07'));
    $this->assertEquals($this->event->isSameYear(), FALSE);
  }

  public function testEventIsSameMonth() {
    $this->assertEquals($this->event->isSameMonth(), TRUE);

    $this->event->setEndDate(new \DateTime('2016-02-10 07:07'));
    $this->assertEquals($this->event->isSameMonth(), FALSE);
  }

  public function testEventIsSameDay() {
    $this->assertEquals($this->event->isSameDay(), FALSE);

    $this->event->setEndDate(new \DateTime('2016-01-1 07:07'));
    $this->assertEquals($this->event->isSameDay(), TRUE);
  }

  public function testEventIsSameHour() {
    $this->assertEquals($this->event->isSameHour(), FALSE);

    $this->event->setEndDate(new \DateTime('2016-01-1 12:07'));
    $this->assertEquals($this->event->isSameHour(), TRUE);
  }

  public function testEventIsFirstMonth() {
    $date = new \DateTime('2016-01-1 12:07');
    $this->assertEquals($this->event->isFirstMonth($date), TRUE);

    $date = new \DateTime('2016-02-2 12:07');
    $this->assertEquals($this->event->isFirstMonth($date), FALSE);
  }

  public function testEventIsLastMonth() {
    $date = new \DateTime('2016-01-1 12:07');
    $this->assertEquals($this->event->isLastMonth($date), TRUE);

    $date = new \DateTime('2016-02-2 12:07');
    $this->assertEquals($this->event->isLastMonth($date), FALSE);
  }

  public function testEventIsFirstDay() {
    $date = new \DateTime('2016-01-1 12:07');
    $this->assertEquals($this->event->isFirstDay($date), TRUE);

    $date = new \DateTime('2016-01-2 12:07');
    $this->assertEquals($this->event->isFirstDay($date), FALSE);
  }

  public function testEventIsFirsHour() {
    $date = new \DateTime('2016-01-1 12:07');
    $this->assertEquals($this->event->isFirstHour($date), TRUE);

    $date = new \DateTime('2016-01-1 13:07');
    $this->assertEquals($this->event->isFirstHour($date), FALSE);
  }

  public function testEventDateDiff() {
    $this->assertEquals($this->event->diff()->days, 8);
  }

  public function testEventDateIsInRange() {
    // In range
    $start = new \DateTime('2016-01-05 10:10');
    $this->assertEquals($this->event->dateIsInRange($start), TRUE);

    // Out of range later
    $start = new \DateTime('2016-01-11 10:10');
    $this->assertEquals($this->event->dateIsInRange($start), FALSE);

    // Same end time
    $start = new \DateTime('2016-01-10 07:07');
    $this->assertEquals($this->event->dateIsInRange($start), TRUE);

    // Same start time
    $start = new \DateTime('2016-01-01 12:12');
    $this->assertEquals($this->event->dateIsInRange($start), TRUE);

    // Earlier time
    $start = new \DateTime('2016-01-01 12:11');
    $this->assertEquals($this->event->dateIsInRange($start), FALSE);
  }

  public function testEventDateIsEarlier() {
    // In range
    $start = new \DateTime('2016-01-05 10:10');
    $this->assertEquals($this->event->dateIsEarlier($start), FALSE);

    // Out of range later
    $start = new \DateTime('2016-01-11 10:10');
    $this->assertEquals($this->event->dateIsEarlier($start), FALSE);

    // Same end time
    $start = new \DateTime('2016-01-10 07:07');
    $this->assertEquals($this->event->dateIsEarlier($start), FALSE);

    // Same start time
    $start = new \DateTime('2016-01-01 12:12');
    $this->assertEquals($this->event->dateIsEarlier($start), FALSE);

    // Earlier time
    $start = new \DateTime('2016-01-01 12:11');
    $this->assertEquals($this->event->dateIsEarlier($start), TRUE);
  }

  public function testEventDateIsLater() {
    // In range
    $start = new \DateTime('2016-01-05 10:10');
    $this->assertEquals($this->event->dateIsLater($start), FALSE);

    // Out of range later
    $start = new \DateTime('2016-01-11 10:10');
    $this->assertEquals($this->event->dateIsLater($start), TRUE);

    // Same end time
    $start = new \DateTime('2016-01-10 07:07');
    $this->assertEquals($this->event->dateIsLater($start), FALSE);

    // Same start time
    $start = new \DateTime('2016-01-01 12:12');
    $this->assertEquals($this->event->dateIsLater($start), FALSE);

    // Earlier time
    $start = new \DateTime('2016-01-01 12:11');
    $this->assertEquals($this->event->dateIsLater($start), FALSE);
  }


  public function testEventOverlap() {
    // Complete Overlap
    $start = new \DateTime('2016-01-01 12:12');
    $end = new \DateTime('2016-01-10 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), TRUE);

    // Starts Earlier, ends in Range
    $start = new \DateTime('2015-12-31 12:12');
    $end = new \DateTime('2016-01-4 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), TRUE);

    // Starts Earlier, ends out of range
    $start = new \DateTime('2015-12-31 12:12');
    $end = new \DateTime('2016-05-10 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), TRUE);

    // Starts in Range, ends in range
    $start = new \DateTime('2016-01-03 12:12');
    $end = new \DateTime('2016-01-9 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), TRUE);

    // Starts in Range, ends later
    $start = new \DateTime('2016-01-03 12:12');
    $end = new \DateTime('2016-01-11 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), TRUE);


    // All later
    $start = new \DateTime('2016-02-03 12:12');
    $end = new \DateTime('2016-02-11 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), FALSE);

    // All earlier
    $start = new \DateTime('2015-02-03 12:12');
    $end = new \DateTime('2015-02-11 07:07');
    $this->assertEquals($this->event->overlaps($start, $end), FALSE);
  }

  public function testEventStartsEarlier() {
    // Later than event start
    $start = new \DateTime('2016-01-02 12:12');
    $this->assertEquals($this->event->startsEarlier($start), TRUE);

    // Earlier than event start
    $start = new \DateTime('2016-01-01 10:12');
    $this->assertEquals($this->event->startsEarlier($start), FALSE);

    // Same as event start
    $start = new \DateTime('2016-01-01 12:12');
    $this->assertEquals($this->event->startsEarlier($start), FALSE);
  }

  public function testEventEndsLater() {
    // Later than event end
    $end = new \DateTime('2016-02-11 07:07');
    $this->assertEquals($this->event->endsLater($end), FALSE);

    // Earlier than event end
    $end = new \DateTime('2016-01-10 07:06');
    $this->assertEquals($this->event->endsLater($end), TRUE);

    // Same as event end
    $end = new \DateTime('2016-01-10 07:07');
    $this->assertEquals($this->event->endsLater($end), FALSE);

  }

  public function testEventItemizeEventDifferentDays() {

    $itemized = $this->event->itemize(new EventItemizer($this->event));

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

  public function testEventItemizeEventOneMinuteApart() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-01 12:13');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d1'], '-1');

    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');

    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m12'], '5');
    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m13'], '5');
  }

  public function testEventItemizeEventSameMinute() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-01-01 12:12');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d1'], '-1');

    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');

    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['1']['d1']['h12']['m12'], '5');
  }

  public function testEventItemizeEventTwoMonths() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 12:12');
    $end_date = new \DateTime('2016-03-01 23:59');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    // First day should be -1
    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d1'], '-1');

    // Every other day of January should be 5
    for ($i = 2; $i <=31; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d'.$i], '5');
    }

    // Every day of February should be 5
    for ($i = 1; $i <=29; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['2']['d'.$i], '5');
    }

    // The first day of March should be 5 because whole day
    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['3']['d1'], '5');

    // The 12th hour of the first day should be -1
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h12'], '-1');

    // Every hour of the first day from there onwards should be 5
    for ($i = 13; $i <= 23; $i++) {
      $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d1']['h' . $i], '5');
    }

    // THe last day hour should be empty
    $this->assertEquals(count($itemized[Event::BAT_HOUR]['2016']['3']['d1']), 0);
  }

  public function testEventItemizeEventTwoMonthsStartMidnight() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-01 00:00');
    $end_date = new \DateTime('2016-03-01 23:59');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    // First day should be 5
    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d1'], '5');

    // Every other day of January should be 5
    for ($i = 2; $i <=31; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['1']['d'.$i], '5');
    }

    // Every day of February should be 5
    for ($i = 1; $i <=29; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['2']['d'.$i], '5');
    }

    // The first day of March should be 5 because whole day
    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['3']['d1'], '5');

    
    // THe last day hour should be empty
    $this->assertEquals(count($itemized[Event::BAT_HOUR]['2016']['3']['d1']), 0);
  }

  public function testEventItemizeIncludingTwoYearsAndFebruary() {
    $event_state = 5;
    $start_date = new \DateTime('2015-12-31 10:00');
    $end_date = new \DateTime('2016-04-30 12:12');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    // First day should be -1
    $this->assertEquals($itemized[Event::BAT_DAY]['2015']['12']['d31'], '-1');

    // Hours 10am to 23pm included should be 5
    for ($i = 10; $i <=23; $i++) {
      $this->assertEquals($itemized[Event::BAT_HOUR]['2015']['12']['d31']['h'.$i], '5');
    }

    // Every day of February should be 5
    for ($i = 1; $i <=29; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['2']['d'.$i], '5');
    }

    // Every day of March should be 5
    for ($i = 1; $i <=31; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['3']['d'.$i], '5');
    }

    // The first 29 days of April should be 5
    for ($i = 1; $i <=29; $i++) {
      $this->assertEquals($itemized[Event::BAT_DAY]['2016']['4']['d'.$i], '5');
    }

    // Hours 00am to 11pm included should be 5
    for ($i = 0; $i <=11; $i++) {
      $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['4']['d30']['h'.$i], '5');
    }

    // Hour 12 should be -1
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['4']['d30']['h12'], '-1');

    // Minutes 1-12 should be 5
    for ($i = 0; $i <= 12; $i++) {
      if ($i <= 9) {
        $index = 'm0' . $i;
      }
      else {
        $index = 'm' . $i;
      }
      $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['4']['d30']['h12'][$index], '5');
    }
  }

  public function testEndOfMonthEventItemization() {
    $event_state = 5;
    $start_date = new \DateTime('2016-04-30 00:00');
    $end_date = new \DateTime('2016-04-30 00:00');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $mock_event = $event->itemize(new EventItemizer($event, Event::BAT_DAILY));

    // First day should be -1
    $this->assertEquals($mock_event[Event::BAT_DAY]['2016']['4']['d30'], '5');

  }

  public function testEventItemizeEventEndMidnight() {
    $event_state = 5;
    $start_date = new \DateTime('2016-03-01 21:00');
    $end_date = new \DateTime('2016-03-01 23:59');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['3']['d1'], '-1');

    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['3']['d1']['h21'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['3']['d1']['h22'], '5');
    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['3']['d1']['h23'], '5');

    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['3']['d1']['h21']['m00'], '5');
    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['3']['d1']['h23']['m59'], '5');
  }

  public function testEventItemizeEventOneDay() {
    $event_state = 5;
    $start_date = new \DateTime('2016-03-01 00:00');
    $end_date = new \DateTime('2016-03-01 23:59');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemized = $event->itemize(new EventItemizer($event));

    $this->assertEquals($itemized[Event::BAT_DAY]['2016']['3']['d1'], '5');
  }

  public function testCreateHourlyGranular() {
    $event_state = 5;
    $start_date = new \DateTime('2016-01-30 12:12');
    $end_date = new \DateTime('2016-02-02 12:12');
    $unit = new Unit(1, 2, array());

    $event = new Event($start_date, $end_date, $unit, $event_state);

    $itemizer = new EventItemizer($event);
    $itemized = $itemizer->createHourlyGranular($start_date, $end_date, new \DateInterval('PT1M'));

    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['1']['d30']['h12'], '-1');

    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['1']['d30']['h12']['m12'], '5');

    $this->assertEquals($itemized[Event::BAT_HOUR]['2016']['2']['d2']['h12'], '-1');

    $this->assertEquals($itemized[Event::BAT_MINUTE]['2016']['2']['d2']['h12']['m11'], '5');
  }

}
