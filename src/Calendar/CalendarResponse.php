<?php

/**
 * @file
 * Class CalendarResponse
 */

namespace Roomify\Bat\Calendar;

use Roomify\Bat\Unit\UnitInterface;
use Roomify\Bat\Constraint\Constraint;

/**
 * A CalendarResponse contains the units that are matched or missed following
 * a search, together with the reason they are matched or missed.
 */
class CalendarResponse {

  const VALID_STATE = 'valid_state';
  const INVALID_STATE = 'invalid_state';
  const CONSTRAINT = 'constraint';

  /**
   * @var array
   */
  protected $included_set;

  /**
   * @var array
   */
  protected $excluded_set;

  /**
   * @var \DateTime
   */
  protected $start_date;

  /**
   * @var \DateTime
   */
  protected $end_date;

  /**
   * @var array
   */
  protected $valid_states;

  /**
   * @param $start_date
   * @param $end_date
   * @param $valid_states
   * @param $included
   * @param $excluded
   */
  public function __construct(\DateTime $start_date, \DateTime $end_date, $valid_states, $included = array(), $excluded = array()) {
    $this->start_date = $start_date;
    $this->end_date = $end_date;
    $this->valid_states = $valid_states;
    $this->included_set = $included;
    $this->excluded_set = $excluded;
  }

  /**
   * @param $unit
   * @param $reason
   */
  public function addMatch(UnitInterface $unit, $reason = '') {
    $this->included_set[$unit->getUnitId()] = array(
      'unit' => $unit,
      'reason' => $reason,
    );
  }

  /**
   * @param $unit
   * @param $reason
   */
  public function addMiss(UnitInterface $unit, $reason = '', Constraint $constraint = NULL) {
    $this->excluded_set[$unit->getUnitId()] = array(
      'unit' => $unit,
      'reason' => $reason,
    );

    if ($constraint !== NULL) {
      $this->excluded_set[$unit->getUnitId()]['constraint'] = $constraint;
    }
  }

  /**
   * @return array
   */
  public function getIncluded() {
    return $this->included_set;
  }

  /**
   * @return array
   */
  public function getExcluded() {
    return $this->excluded_set;
  }

  /**
   * @return DateTime
   */
  public function getStartDate() {
    return $this->start_date;
  }

  /**
   * @return DateTime
   */
  public function getEndDate() {
    return $this->end_date;
  }

  /**
   * @param $unit
   * @param $reason
   *
   * @return bool
   */
  public function removeFromMatched(UnitInterface $unit, $reason = '', Constraint $constraint = NULL) {
    if (isset($this->included_set[$unit->getUnitId()])) {
      // Remove a unit from matched and add to the missed set
      unset($this->included_set[$unit->getUnitId()]);
      $this->addMiss($unit, $reason, $constraint);
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * @param $constraints
   */
  public function applyConstraints($constraints) {
    foreach ($constraints as $constraint) {
      $constraint->applyConstraint($this);
    }
  }

}
