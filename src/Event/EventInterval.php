<?php

/**
 * @file
 * Class EventInterval
 */

namespace Roomify\Bat\Event;

class EventInterval {

	/**
	 * Return how many times $duration is in the interval
	 * between $start_date and $end_date
	 *
	 * @param \DateTime $start_date
	 * @param \DateTime $end_date
	 * @param \DateInterval $duration
	 *
	 * @return float
	 */
	public static function divide(\DateTime $start_date, \DateTime $end_date, \DateInterval $duration) {
		$temp_end_date = clone($end_date);
		$temp_end_date->add(new \DateInterval('PT1M'));

		$duration_seconds = $duration->d * 86400 + $duration->h * 3600 + $duration->i * 60 + $duration->s;

		return ($temp_end_date->getTimestamp() - $start_date->getTimestamp()) / $duration_seconds;
	}

}
