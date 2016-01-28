<?php

/**
 * @file
 * Interface Valuator
 */

namespace Roomify\Bat\Valuator;

/**
 * A Valuator applies a specific valuation strategy (e.g. price per night per unit)
 * to a set of events and produces a final value at the end.
 */
interface ValuatorInterface {

  /**
   * Given a set of events it will determine the value based on the specific
   * implementation
   *
   * @return float value
   */
  public function determineValue();

}
