<?php

/**
 * @file
 * Class AbstractUnit
 */

namespace Roomify\Bat\Unit;

use Roomify\Bat\Unit\UnitInterface;

abstract class AbstractUnit implements UnitInterface {

  /**
   * @var
   */
  protected $unit_id;

  /**
   * @var
   */
  protected $default_value;

  /**
   * @var
   */
  protected $constraints;

  /**
   * {@inheritdoc}
   */
  public function getUnitId() {
    return $this->unit_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitId($unit_id) {
    $this->unit_id = $unit_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue() {
    return (int) $this->default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValue($default_value) {
    $this->default_value = $default_value;
  }

  /**
   * {@inheritdoc}
   */
  public function setConstraints($constraints) {
    $this->constraints = $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    return $this->constraints;
  }

}
