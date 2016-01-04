<?php

namespace Roomify\Bat\Test;

use Roomify\Bat\Unit\Unit;

class UnitTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test Unit.
   */
  public function testUnit() {
    $unit = new Unit(1, 2, array());

    $this->assertEquals($unit->getUnitId(), 1);
    $this->assertEquals($unit->getDefaultValue(), 2);

    $unit->setUnitId(3);
    $this->assertEquals($unit->getUnitId(), 3);

    $unit->setDefaultValue(4);
    $this->assertEquals($unit->getDefaultValue(), 4);
  }

}
