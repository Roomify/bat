<?php

/**
 * @file
 * Interface UnitInterface
 */

namespace Roomify\Bat\Unit;

/**
 * The basic BAT unit interface.
 */
interface UnitInterface {

  /**
   * Returns the unit id.
   * @return int
   */
  public function getUnitId();

  /**
   * Sets the unit id.
   * @param $unit_id
   */
  public function setUnitId($unit_id);

  /**
   * Return the default value this unit should have. We do not define here the
   * event type - this should be deal by whoever is instantiating the unit.
   * @return int
   */
  public function getDefaultValue();

  /**
   * Sets the default value.
   *
   * @param $default_value
   */
  public function setDefaultValue($default_value);

  /**
   * @param $constraints
   */
  public function setConstraints($constraints);

  /**
   * @return
   */
  public function getConstraints();

}
