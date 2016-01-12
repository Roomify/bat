<?php

/**
 * @file
 * Interface EventFormatter
 */

namespace Roomify\Bat\EventFormatter;

use Roomify\Bat\Event\EventInterface;

interface EventFormatter {

  /**
   * @param \Roomify\Bat\Event\EventInterface $event
   */
  public function format(EventInterface $event);

}
