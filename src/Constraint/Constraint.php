<?php

/**
 * @file
 * Class Constraint
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Constraint\ConstraintInterface;


/**
 * A constraint acts as a filter that can be applied to a Calendar Response to
 * further reduce the set of matching units based on criteria beyond their
 * specific state over the time range the Calendar was queried.
 */
class Constraint extends AbstractConstraint {

  /**
   * @param $units
   */
  public function __construct($units = array()) {
    $this->units = $units;
  }

}
