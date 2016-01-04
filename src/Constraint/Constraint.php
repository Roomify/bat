<?php

/**
 * @file
 * Class Constraint
 */

namespace Roomify\Bat\Constraint;

use Roomify\Bat\Constraint\ConstraintInterface;


/**
 * A constraint acts as a filter that can be applied to a Calendar Response to
 * further reduce the set of matching units. Constraints allow developers to add
 * additional criteria that go beyond the specific value a unit finds itself in for
 * a give time range.
 */
class Constraint extends AbstractConstraint {

  /**
   * @param $units
   */
  public function __construct($units = array()) {
    $this->units = $units;
  }

}
