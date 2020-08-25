<?php

namespace Roomify\Bat\Test;

use PHPUnit\Framework\TestCase;

use Roomify\Bat\Event\EventInterval;

class EventIntervalTest extends TestCase {

	public function testDivide1() {
		$start_date = new \DateTime('2016-01-01 00:00');
		$end_date = new \DateTime('2016-01-01 23:59');

		$duration = new \DateInterval('P1D');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 1);

		$duration = new \DateInterval('PT3H');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 8);

		$duration = new \DateInterval('PT2H');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 12);

		$duration = new \DateInterval('PT1H');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 24);

		$duration = new \DateInterval('PT30M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 48);

		$duration = new \DateInterval('PT15M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 96);

		$duration = new \DateInterval('PT10M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 144);

		$duration = new \DateInterval('PT5M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 288);
	}

	public function testDivide2() {
		$start_date = new \DateTime('2016-02-11 12:00');
		$end_date = new \DateTime('2016-02-11 21:59');

		$duration = new \DateInterval('P1D');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 0.42);

		$duration = new \DateInterval('PT3H');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 3.33);

		$duration = new \DateInterval('PT2H');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 5);

		$duration = new \DateInterval('PT1H');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 10);

		$duration = new \DateInterval('PT30M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 20);

		$duration = new \DateInterval('PT15M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 40);

		$duration = new \DateInterval('PT10M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 60);

		$duration = new \DateInterval('PT5M');
		$percentage = EventInterval::divide($start_date, $end_date, $duration);
		$this->assertEquals(round($percentage, 2), 120);
	}

}
