<?php

/**
 * @file
 * Class ConstraintManager
 */

namespace Roomify\Bat\Constraint;

class ConstraintManager {

  protected $constraints;

  /**
   * @param $constraints
   */
  public function __construct($constraints = array()) {
    $this->constraints = array();
    foreach ($constraints as $constraint) {
      $this->constraints[get_class($constraint)][] = $constraint;
    }
  }

  /**
   * @return array
   */
  public function getConstraints($constraint_class = NULL) {
    if ($constraint_class == NULL) {
      return $this->constraints;
    } else {
      return $this->constraints[$constraint_class];
    }
  }

  /**
   * @param $constraints
   */
  public function addConstraints($constraints) {
    foreach ($constraints as $constraint) {
      $this->constraints[get_class($constraint)][] = $constraint;
    }
  }

  /**
   * @param $constraints
   */
  public function setConstraints($constraints) {
    $this->constraints = array();
    $this->addConstraints($constraints);
  }

  /**
   * @param $constraint_class
   *
   * @return array
   */
  public function normalizeConstraints($constraint_class = NULL) {
    if ($constraint_class == NULL) {
      $classes = array_keys($this->constraints);
    } else {
      if (isset($this->constraints[$constraint_class])) {
        $classes = array($constraint_class);
      } else {
        return array();
      }
    }

    $new_constraints = array();

    foreach ($classes as $class) {
      switch ($class) {
        case 'Roomify\Bat\Constraint\MinMaxDaysConstraint':
          $new_constraints[$class] = $this->normalizeMinMaxDaysConstraints();
          break;

        case 'Roomify\Bat\Constraint\CheckInDayConstraint':
          $new_constraints[$class] = $this->normalizeCheckInDayConstraints();
          break;

        default:
          if (isset($this->constraints[$class])) {
            $new_constraints[$class] = $this->constraints[$class];
          }
      }
    }

    if ($constraint_class == NULL) {
      return $new_constraints;
    } else {
      return $new_constraints[$constraint_class];
    }
  }

  /**
   * @return array
   */
  protected function normalizeMinMaxDaysConstraints() {
    $new_constraints = array();

    $constraints = array_map(function ($object) { return clone $object; }, $this->constraints['Roomify\Bat\Constraint\MinMaxDaysConstraint']);

    foreach (array_reverse($constraints) as $constraint) {
      $start_date = $constraint->getStartDate();
      $end_date = $constraint->getEndDate();

      $split_constraint = NULL;

      if (!empty($new_constraints)) {
        foreach ($new_constraints as $new_constraint) {
          $new_start_date = $new_constraint->getStartDate();
          $new_end_date = $new_constraint->getEndDate();

          if ($constraint->getMinDays() && $new_constraint->getMinDays() ||
              ($constraint->getMaxDays() && $new_constraint->getMaxDays())) {
            if ($start_date >= $new_start_date && $start_date <= $new_end_date) {
              $new_end_date_clone = clone($new_end_date);
              $constraint->setStartDate($new_end_date_clone->add(new \DateInterval('P1D')));
            }
            elseif ($end_date >= $new_start_date && $end_date <= $new_end_date) {
              $new_start_date_clone = clone($new_start_date);
              $constraint->setEndDate($new_start_date_clone->sub(new \DateInterval('P1D')));
            }
            elseif ($start_date < $new_start_date && $end_date > $new_end_date) {
              if ($constraint->getEndDate() > $new_start_date) {
                $new_start_date_clone = clone($new_start_date);
                $constraint->setEndDate($new_start_date_clone->sub(new \DateInterval('P1D')));
              }

              if ($split_constraint == NULL) {
                $split_start_date = clone($new_end_date);
                $split_start_date->add(new \DateInterval('P1D'));
                $split_end_date = $end_date;

                $split_constraint = new MinMaxDaysConstraint($constraint->getUnits(), $constraint->getMinDays(), $constraint->getMaxDays(), $split_start_date, $split_end_date, $constraint->getCheckinDay());
              }
              else {
                $split_start_date = $split_constraint->getStartDate();
                $split_end_date = $split_constraint->getEndDate();

                if ($split_start_date < $new_end_date) {
                  $new_end_date_clone = clone($new_end_date);
                  $split_constraint->setStartDate($new_end_date_clone->add(new \DateInterval('P1D')));
                }
                if ($split_end_date < $new_start_date) {
                  $new_start_date_clone = clone($new_start_date);
                  $split_constraint->setEndDate($new_start_date_clone->sub(new \DateInterval('P1D')));
                }
              }
            }
          }
        }

        if ($split_constraint != NULL) {
          $new_constraints[] = $split_constraint;
        }
      }

      $new_constraints[] = $constraint;
    }

    foreach ($new_constraints as $i => $constraint) {
      if ($constraint->getStartDate() > $constraint->getEndDate()) {
        unset($new_constraints[$i]);
      }
    }

    return $new_constraints;
  }

  /**
   * @return array
   */
  protected function normalizeCheckInDayConstraints() {
    $new_constraints = array();

    $constraints = array_map(function ($object) { return clone $object; }, $this->constraints['Roomify\Bat\Constraint\CheckInDayConstraint']);

    foreach (array_reverse($constraints) as $constraint) {
      $start_date = $constraint->getStartDate();
      $end_date = $constraint->getEndDate();

      $split_constraint = NULL;

      if (!empty($new_constraints)) {
        foreach ($new_constraints as $new_constraint) {
          $new_start_date = $new_constraint->getStartDate();
          $new_end_date = $new_constraint->getEndDate();

          if ($constraint->getCheckinDay() && $new_constraint->getCheckinDay()) {
            if ($start_date >= $new_start_date && $start_date <= $new_end_date) {
              $new_end_date_clone = clone($new_end_date);
              $constraint->setStartDate($new_end_date_clone->add(new \DateInterval('P1D')));
            } elseif ($end_date >= $new_start_date && $end_date <= $new_end_date) {
              $new_start_date_clone = clone($new_start_date);
              $constraint->setEndDate($new_start_date_clone->sub(new \DateInterval('P1D')));
            } elseif ($start_date < $new_start_date && $end_date > $new_end_date) {
              if ($constraint->getEndDate() > $new_start_date) {
                $new_start_date_clone = clone($new_start_date);
                $constraint->setEndDate($new_start_date_clone->sub(new \DateInterval('P1D')));
              }

              if ($split_constraint == NULL) {
                $split_start_date = clone($new_end_date);
                $split_start_date->add(new \DateInterval('P1D'));
                $split_end_date = $end_date;

                $split_constraint = new CheckInDayConstraint($constraint->getUnits(), $constraint->getCheckinDay(), $split_start_date, $split_end_date);
              } else {
                $split_start_date = $split_constraint->getStartDate();
                $split_end_date = $split_constraint->getEndDate();

                if ($split_start_date < $new_end_date) {
                  $new_end_date_clone = clone($new_end_date);
                  $split_constraint->setStartDate($new_end_date_clone->add(new \DateInterval('P1D')));
                }
                if ($split_end_date < $new_start_date) {
                  $new_start_date_clone = clone($new_start_date);
                  $split_constraint->setEndDate($new_start_date_clone->sub(new \DateInterval('P1D')));
                }
              }
            }
          }
        }

        if ($split_constraint != NULL) {
          $new_constraints[] = $split_constraint;
        }
      }

      $new_constraints[] = $constraint;
    }

    foreach ($new_constraints as $i => $constraint) {
      if ($constraint->getStartDate() > $constraint->getEndDate()) {
        unset($new_constraints[$i]);
      }
    }

    return $new_constraints;
  }
  
}
