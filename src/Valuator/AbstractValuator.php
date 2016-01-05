<?php

/**
 * @file
 * Abstract Valuator
 */

namespace Roomify\Bat\Valuator;

use Roomify\Bat\Valuator\ValuatorInterface;

abstract class AbstractValuator implements ValuatorInterface {

  /**
   * The start date of the period over which we should reason about value
   *
   * @var /DateTime
   */
  protected $start_date;


  /**
   * The end date of the period over which we should reason about value
   * @var /DateTime
   */
  protected $end_date;


  /**
   * The unit involved
   * @var
   */
  protected $unit;
}
