<?php

/**
 * @file
 * Class AbstractEvent
 */

namespace Roomify\Bat\Event;

use Roomify\Bat\Event\EventInterface;
use Roomify\Bat\Store\Store;
use Roomify\Bat\EventFormatter\EventFormatter;
use Roomify\Bat\Store\StoreInterface;

abstract class AbstractEvent implements EventInterface {

  const BAT_DAY = 'bat_day';
  const BAT_HOUR = 'bat_hour';
  const BAT_MINUTE = 'bat_minute';
  const BAT_HOURLY = 'bat_hourly';
  const BAT_DAILY = 'bat_daily';

  /**
   * The booking unit the event is relevant to
   * @var int
   */
  protected $unit_id;

  /**
   * The unit the event is relevant to
   */
  protected $unit;

  /**
   * The start date for the event.
   *
   * @var \DateTime
   */
  protected $start_date;

  /**
   * The end date for the event. Keep in mind that BAT considers a time such as
   * 1358 to include the entire 58th minute. So what an event that we would describe
   * as starting at 1300 and ending at 1400 for BAT actually ends at 1359. This is because
   * (among other reasons) there may well be another event starting at 1400 and two events
   * next to each other cannot share the same time.
   *
   * @var \DateTime
   */
  protected $end_date;

  /**
   * The value associated with this event.
   * This can represent an availability state or a pricing value
   *
   * @var int
   */
  protected $value;

  /**
   * Returns the value.
   *
   * @return int
   */
  public function getValue() {
    return (int) $this->value;
  }

  /**
   * Set the value.
   *
   * @param int $value
   */
  public function setValue($value) {
    $this->value = $value;
  }

  /**
   * Returns the unit id.
   *
   * @return int
   */
  public function getUnitId() {
    return $this->unit_id;
  }

  /**
   * Set the unit id.
   *
   * @param int $unit_id
   */
  public function setUnitId($unit_id) {
    $this->unit_id = $unit_id;
  }

  /**
   * Returns the start date.
   *
   * @return \DateTime
   */
  public function getStartDate() {
    return clone($this->start_date);
  }

  /**
   * Utility function to always give us a standard format for viewing the start date.
   * @return mixed
   */
  public function startDateToString($format = 'Y-m-d H:i') {
    return $this->start_date->format($format);
  }

  /**
   * Set the start date.
   *
   * @param \DateTime $start_date
   */
  public function setStartDate(\DateTime $start_date) {
    $this->start_date = clone($start_date);
  }

  /**
   * Returns the end date.
   *
   * @return \DateTime
   */
  public function getEndDate() {
    return clone($this->end_date);
  }

  /**
   * Utility function to always give us a standard format for viewing the end date.
   * @return mixed
   */
  public function endDateToString($format = 'Y-m-d H:i') {
    return $this->end_date->format($format);
  }

  /**
   * Set the end date.
   *
   * @param \DateTime $end_date
   */
  public function setEndDate(\DateTime $end_date) {
    $this->end_date = clone($end_date);
  }

  /**
   * {@inheritdoc}
   */
  public function startDay($format = 'j') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endDay($format = 'j') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startMonth($format = 'n') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endMonth($format = 'n') {
    return $this->end_date->format($format);
  }

  /**
   *{@inheritdoc)
   */
  public function endMonthDate(\DateTime $date) {
    // The time is added so that the end date is included
    $date_format = $date->format('Y-n-t 23:59:59');
    return new \DateTime($date_format);
  }

  /**
   * {@inheritdoc}
   */
  public function startYear($format = 'Y') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endYear($format = 'Y') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startWeek($format = 'W') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endWeek($format = 'W') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startHour($format = 'H') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endHour($format = 'H') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function startMinute($format = 'i') {
    return $this->start_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function endMinute($format = 'i') {
    return $this->end_date->format($format);
  }

  /**
   * {@inheritdoc}
   */
  public function isFirstMonth($date) {
    if ($date->format("n") == $this->startMonth() && $date->format("Y") == $this->startYear()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isLastMonth($date) {
    if ($date->format("n") == $this->endMonth() && $date->format("Y") == $this->endYear()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFirstDay($date) {
    if (($date->format('j') == $this->startDay()) && ($this->isFirstMonth($date))) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isFirstHour($date) {
    if ($date->format('G') == $this->startHour() && $this->isFirstDay($date)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameYear() {
    if ($this->startYear() == $this->endYear()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameMonth() {
    if (($this->startMonth() == $this->endMonth()) && $this->isSameYear()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameDay() {
    if (($this->startDay() == $this->endDay()) && $this->isSameMonth()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSameHour() {
    if (($this->startHour() == $this->endHour()) && $this->isSameDay()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function diff() {
    $interval = $this->start_date->diff($this->end_date);
    return $interval;
  }

  /**
   * Returns true if the event overlaps at all with the time period within
   * the start and end time.
   *
   * @param \DateTime $start
   * @param \DateTime $end
   * @return bool
   */
  public function overlaps(\DateTime $start, \DateTime $end) {
    $overlaps = FALSE;

    if ($this->dateIsEarlier($start) &&
      ($this->dateIsInRange($end) || $this->dateIsLater($end))) {
      $overlaps = TRUE;
    } elseif ($this->dateIsInRange($start) &&
      ($this->dateIsInRange($end) || $this->dateIsLater($end))) {
      $overlaps = TRUE;
    }

    return $overlaps;
  }

  /**
   * Checks if date supplied is in range of event
   *
   * @param \DateTime $date
   * @return bool
   */
  public function dateIsInRange(\DateTime $date) {
    $dateInRange = FALSE;

    $t1 = $this->start_date->getTimeStamp();
    $t2 = $this->end_date->getTimeStamp();

    $t3 = $date->getTimeStamp();

    if (($t3 >= $t1) && ($t3 <= $t2)) {
      $dateInRange = TRUE;
    }

    return $dateInRange;
  }

  /**
   * Checks if the date supplied starts earlier than our event
   * @param \DateTime $date
   * @return bool
   */
  public function dateIsEarlier(\DateTime $date) {
    $dateEarlier = FALSE;

    $t1 = $this->start_date->getTimeStamp();

    $t3 = $date->getTimeStamp();

    if ($t3 < $t1) {
      $dateEarlier = TRUE;
    }

    return $dateEarlier;
  }

  /**
   * Checks if the date supplied ends after our event ends
   * @param \DateTime $date
   * @return bool
   */
  public function dateIsLater(\DateTime $date) {
    $dateLater = FALSE;

    $t2 = $this->end_date->getTimeStamp();

    $t4 = $date->getTimestamp();

    if ($t2 < $t4) {
      $dateLater = TRUE;
    }

    return $dateLater;
  }

  /**
   * Checks if our event ends after the date supplied
   * @param \DateTime $date
   * @return bool
   */
  public function endsLater(\DateTime $date) {
    $later = FALSE;

    $t2 = $this->end_date->getTimeStamp();

    $t4 = $date->getTimestamp();

    if ($t2 > $t4) {
      $later = TRUE;
    }

    return $later;
  }

  /**
   * Checks if our event starts earlier than the date supplied
   * @param \DateTime $date
   * @return bool
   */
  public function startsEarlier(\DateTime $date) {
    $earlier = FALSE;

    $t1 = $this->start_date->getTimeStamp();

    $t3 = $date->getTimestamp();

    if ($t1 < $t3) {
      $earlier = TRUE;
    }

    return $earlier;
  }

  /**
   * Transforms the event in a breakdown of days, hours and minutes with associated states.
   *
   * @param EventItemizer $itemizer
   * @return array
   */
  public function itemize($itemizer) {
    $itemized = $itemizer->itemizeEvent();
    return $itemized;
  }

  /**
   * Saves an event using the Store object
   *
   * @param StoreInterface $store
   * @param string $granularity
   *
   * @return boolean
   */
  public function saveEvent(StoreInterface $store, $granularity = AbstractEvent::BAT_HOURLY) {
    return $store->storeEvent($this, $granularity);
  }

  /**
   * {@inheritdoc}
   */
  public function toJson(EventFormatter $event_formatter) {
    return $event_formatter->format($this);
  }

}
