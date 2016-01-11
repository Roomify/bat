<?php

/**
 * @file
 * Interface ConstraintInterface
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Calendar\CalendarResponse;

/**
 * The Constraint Interface
 */
interface ConstraintInterface {

  /**
   * Applies the Constraint to a Calendar Response.
   *
   * @param $calendar_response
   */
  public function applyConstraint(CalendarResponse &$calendar_response);

  /**
   * @param $start_date
   */
  public function setStartDate(\DateTime $start_date);

  /**
   * @return \DateTime
   */
  public function getStartDate();

  /**
   * @param $end_date
   */
  public function setEndDate(\DateTime $end_date);

  /**
   * @return \DateTime
   */
  public function getEndDate();

  /**
   * @return
   */
  public function getAffectedUnits();

  /**
   * @return
   */
  public function getUnits();

}
