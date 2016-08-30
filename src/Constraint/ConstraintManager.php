<?php

/**
 * @file
 * Class ConstraintManager
 */

namespace Roomify\Bat\Constraint;

class ConstraintManager {

  /**
   * @param $constraints
   *
   * @return array
   */
  public static function normalizeConstraints($constraints) {
    $new_constraints = array();

    foreach (array_reverse($constraints) as $constraint) {
      $start_date = $constraint->getStartDate();
      $end_date = $constraint->getEndDate();

      if (get_class($constraint) == 'Roomify\Bat\Constraint\MinMaxDaysConstraint') {
        $split_constraint = NULL;

        if (!empty($new_constraints)) {
          foreach ($new_constraints as $new_constraint) {
            if (get_class($new_constraint) == 'Roomify\Bat\Constraint\MinMaxDaysConstraint') {
              $new_start_date = $new_constraint->getStartDate();
              $new_end_date = $new_constraint->getEndDate();

              if ($constraint->getMinDays() && $new_constraint->getMinDays() ||
                  ($constraint->getMaxDays() && $new_constraint->getMaxDays())) {
                if ($start_date >= $new_start_date && $start_date <= $new_end_date) {
                  $constraint->setStartDate(clone($new_end_date)->add(new \DateInterval('P1D')));
                }
                elseif ($end_date >= $new_start_date && $end_date <= $new_end_date) {
                  $constraint->setEndDate(clone($new_start_date)->sub(new \DateInterval('P1D')));
                }
                elseif ($start_date < $new_start_date && $end_date > $new_end_date) {
                  if ($constraint->getEndDate() > $new_start_date) {
                    $constraint->setEndDate(clone($new_start_date)->sub(new \DateInterval('P1D')));
                  }

                  if ($split_constraint == NULL) {
                    $split_start_date = clone($new_end_date)->add(new \DateInterval('P1D'));
                    $split_end_date = $end_date;

                    $split_constraint = new MinMaxDaysConstraint($constraint->getUnits(), $constraint->getMinDays(), $constraint->getMaxDays(), $split_start_date, $split_end_date , $constraint->getCheckinDay());
                  }
                  else {
                    $split_start_date = $split_constraint->getStartDate();
                    $split_end_date = $split_constraint->getEndDate();

                    if ($split_start_date < $new_end_date) {
                      $split_constraint->setStartDate(clone($new_end_date)->add(new \DateInterval('P1D')));
                    }
                    if ($split_end_date < $new_start_date) {
                      $split_constraint->setEndDate(clone($new_start_date)->sub(new \DateInterval('P1D')));
                    }
                  }
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
