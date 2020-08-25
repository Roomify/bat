<?php

namespace Roomify\Bat\Test;

use PHPUnit\Framework\TestCase;

use Roomify\Bat\Unit\Unit;

class UnitTest extends TestCase {

  private $unit;

  public function setUp() {
    $this->unit = new Unit(1, 2, array());
  }

  public function testUnitGetUnitId() {
    $this->assertEquals($this->unit->getUnitId(), 1);
  }

  public function testUnitGetDefaultValue() {
    $this->assertEquals($this->unit->getDefaultValue(), 2);
  }

  public function testUnitSetUnitId() {
    $this->unit->setUnitId(3);
    $this->assertEquals($this->unit->getUnitId(), 3);
  }

  public function testUnitSetDefaultValue() {
    $this->unit->setDefaultValue(4);
    $this->assertEquals($this->unit->getDefaultValue(), 4);
  }

}
