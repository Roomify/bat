<?php

/**
 * @file
 * Class AbstractConstraint
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Constraint\ConstraintInterface;

/**
 * A constraint acts as a filter that can be applied to a Calendar Response to
 * further reduce the set of matching units based on criteria beyond their
 * specific state over the time range the Calendar was queried.
 */
abstract class AbstractConstraint implements ConstraintInterface {

  /**
   * @var DateTime
   */
  protected $start_date;

  /**
   * @var DateTime
   */
  protected $end_date;

  /**
   * @var array
   */
  protected $valid_states;

  /**
   * @var array
   */
  protected $affected_units;

  /**
   * @var CalendarResponse
   */
  protected $calendar_response;

  /**
   * @var array
   */
  protected $units = array();

  /**
   * {@inheritdoc}
   */
  public function setStartDate(\DateTime $start_date) {
    $this->start_date = $start_date;
  }

  /**
   * {@inheritdoc}
   */
  public function getStartDate() {
    return $this->start_date;
  }

  /**
   * {@inheritdoc}
   */
  public function setEndDate(\DateTime $end_date) {
    $this->end_date = $end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function getEndDate() {
    return $this->end_date;
  }

  /**
   * {@inheritdoc}
   */
  public function setValidStates($valid_states) {
    $this->valid_states = $valid_states;
  }

  /**
   * {@inheritdoc}
   */
  public function getValidStates() {
    return $this->valid_states;
  }

  /**
   * {@inheritdoc}
   */
  public function getAffectedUnits() {
    return $this->affected_units;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnits() {
    $keyed_units = array();
    foreach ($this->units as $unit) {
      $keyed_units[$unit->unit_id] = $unit;
    }

    return $keyed_units;
  }

  /**
   * {@inheritdoc}
   */
  public function applyConstraint(&$calendar_response) {
    $this->calendar_response = $calendar_response;
  }

}
