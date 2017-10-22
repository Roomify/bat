<?php

/**
 * @file
 * Class AbstractValuator
 */

namespace Roomify\Bat\Valuator;

use Roomify\Bat\Store\Store;
use Roomify\Bat\Valuator\ValuatorInterface;
use Roomify\Bat\Unit\UnitInterface;

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

  /**
   * The store from which to retrieve event value data
   *
   * @var Store
   */
  protected $store;

  /**
   * AbstractValuator constructor.
   * @param \DateTime $start_date
   * @param \DateTime $end_date
   * @param \Roomify\Bat\Unit\UnitInterface $unit
   */
  public function __construct(\DateTime $start_date, \DateTime $end_date, UnitInterface $unit, Store $store) {
    $this->start_date = clone($start_date);
    $this->end_date = clone($end_date);
    $this->unit = $unit;
    $this->store = $store;
  }

  /**
   * @param \DateTime $start_date
   */
  public function setStartDate(\DateTime $start_date) {
    $this->start_date = clone($start_date);
  }

  /**
   * @return \DateTime
   */
  public function getStartDate() {
    return $this->start_date;
  }

  /**
   * @param \DateTime $end_date
   */
  public function setEndDate(\DateTime $end_date) {
    $this->end_date = $end_date;
  }

  /**
   * @return \DateTime
   */
  public function getEndDate() {
    return $this->end_date;
  }

  /**
   * @param \Roomify\Bat\Unit\UnitInterface $unit
   */
  public function setUnit(UnitInterface $unit) {
    $this->unit = $unit;
  }

  /**
   * @return \Roomify\Bat\Unit\UnitInterface
   */
  public function getUnit() {
    return $this->unit;
  }

  /**
   * @param $events
   * @return mixed
   */
  abstract public function determineValue();

}
