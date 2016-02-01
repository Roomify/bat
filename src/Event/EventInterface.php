<?php

/**
 * @file
 * Interface EventInterface
 */

namespace Roomify\Bat\Event;

use Roomify\Bat\EventFormatter\EventFormatter;

/**
 * The basic BAT event interface.
 */
interface EventInterface {

  /**
   * Returns the unit id.
   *
   * @return int
   */
  public function getUnitId();

  /**
   * Set the unit id.
   *
   * @param int $unit_id
   */
  public function setUnitId($unit_id);

  /**
   * Returns the StartDate object
   *
   * @return \DateTime
   */
  public function getStartDate();

  /**
   * Returns the EndDate object
   *
   * @return \DateTime
   */
  public function getEndDate();

  /**
   * Sets the StartDate
   *
   * @param \DateTime
   */
  public function setStartDate(\DateTime $start_date);

  /**
   * Sets the EndDate
   *
   * @param \DateTime
   */
  public function setEndDate(\DateTime $end_date);

  /**
   * Returns the event value.
   *
   * @return int
   */
  public function getValue();

  /**
   * Sets the event value.
   *
   * @param int $value
   */
  public function setValue($value);

  /**
   * Returns the start day.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The day formatted string.
   */
  public function startDay($format = 'j');

  /**
   * Returns the end day.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The day formatted string.
   */
  public function endDay($format = 'j');

  /**
   * Returns the booking start month.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The month formatted string.
   */
  public function startMonth($format = 'n');

  /**
   * Returns the booking end month.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The month formatted string.
   */
  public function endMonth($format = 'n');

  /**
   * Returns the booking start year.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function startYear($format = 'Y');

  /**
   * Returns the booking end year.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function endYear($format = 'Y');

  /**
   * Returns the booking start hour.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function startWeek($format = 'W');

  /**
   * Returns the booking end hour.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function endWeek($format = 'W');

  /**
   * Returns the booking start hour.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function startHour($format = 'H');

  /**
   * Returns the booking end hour.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function endHour($format = 'H');

  /**
   * Returns the booking start minute.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function startMinute($format = 'i');

  /**
   * Returns the booking end minute.
   *
   * @param string $format
   *   The format string to return.
   *
   * @return string
   *   The year formatted string.
   */
  public function endMinute($format = 'i');

  /**
   * Given a month it returns a date object representing the last
   * day of that month
   *
   * @param $date
   *
   * @return \DateTime
   */
  public function endMonthDate(\DateTime $date);


  /**
   * Returns TRUE if the date supplied is the first month of the event
   * @param \DateTime $date
   *
   * @return bool
   */
  public function isFirstMonth($date);

  /**
   * Returns TRUE if the date supplied is the first day of the event
   * @param $date
   *
   * @return bool
   */
  public function isFirstDay($date);

  /**
   * Returns TRUE if the date supplied is the last month of the event
   * @param \DateTime $date
   *
   * @return bool
   */
  public function isLastMonth($date);

  /**
   * Checks if the event starts and ends in the same year.
   *
   * @return bool
   *   TRUE if the event starts and ends in the same year, FALSE otherwise
   */
  public function isSameYear();


  /**
   * Checks if the event starts and ends in the same month.
   *
   * @return bool
   *   TRUE if the event starts and ends in the same month, FALSE otherwise
   */
  public function isSameMonth();

  /**
   * Checks if the event starts and ends in the same day.
   *
   * @return bool
   *   TRUE if the event starts and ends in the same day, FALSE otherwise
   */
  public function isSameDay();

  /**
   * Checks if the event starts and ends in the same hour.
   *
   * @return bool
   *   TRUE if the event starts and ends in the same hour, FALSE otherwise
   */
  public function isSameHour();


  /**
   * Returns the date interval between end and start date.
   *
   * @return bool|DateInterval
   *   The interval between the end and start date.
   */
  public function diff();


  /**
   * Returns the json version of this event.
   *
   * @param \Roomify\Bat\Event\EventFormatter $event_formatter
   *
   * @return mixed
   */
  public function toJson(EventFormatter $event_formatter);

}
