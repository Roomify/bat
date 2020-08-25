<?php

namespace Roomify\Bat\Test;


class SetupStore {

  /**
   * SQLLite syntax to setup a day table
   *
   * @param $table_name
   * @param $type
   * @return string
   */
  public static function createDayTable($table_name, $type) {
    //CREATE TABLE bat_event_availability_event_day_event ('unit_id' INTEGER NOT NULL, 'year' INTEGER NOT NULL DEFAULT 0, 'month' INTEGER NOT NULL DEFAULT '0', 'd1' INTEGER NOT NULL DEFAULT '0', 'd2' INTEGER NOT NULL DEFAULT '0', 'd3' INTEGER NOT NULL DEFAULT '0', 'd4' INTEGER NOT NULL DEFAULT '0', 'd5' INTEGER NOT NULL DEFAULT '0', 'd6' INTEGER NOT NULL DEFAULT '0', 'd7' INTEGER NOT NULL DEFAULT '0', 'd8' INTEGER NOT NULL DEFAULT '0', 'd9' INTEGER NOT NULL DEFAULT '0', 'd10' INTEGER NOT NULL DEFAULT '0', 'd11' INTEGER NOT NULL DEFAULT '0', 'd12' INTEGER NOT NULL DEFAULT '0', 'd13' INTEGER NOT NULL DEFAULT '0', 'd14' INTEGER NOT NULL DEFAULT '0', 'd15' INTEGER NOT NULL DEFAULT '0', 'd16' INTEGER NOT NULL DEFAULT '0', 'd17' INTEGER NOT NULL DEFAULT '0', 'd18' INTEGER NOT NULL DEFAULT '0', 'd19' INTEGER NOT NULL DEFAULT '0', 'd20' INTEGER NOT NULL DEFAULT '0', 'd21' INTEGER NOT NULL DEFAULT '0', 'd22' INTEGER NOT NULL DEFAULT '0', 'd23' INTEGER NOT NULL DEFAULT '0', 'd24' INTEGER NOT NULL DEFAULT '0', 'd25' INTEGER NOT NULL DEFAULT '0', 'd26' INTEGER NOT NULL DEFAULT '0', 'd27' INTEGER NOT NULL DEFAULT '0', 'd28' INTEGER NOT NULL DEFAULT '0', 'd29' INTEGER NOT NULL DEFAULT '0', 'd30' INTEGER NOT NULL DEFAULT '0', 'd31' INTEGER NOT NULL DEFAULT '0', PRIMARY KEY ('unit_id', 'year', 'month'))"

    $command = 'CREATE TABLE ' . 'bat_event_'.$table_name.'_day_'.$type;
    $command .= ' (unit_id INTEGER NOT NULL DEFAULT 0, year INTEGER NOT NULL DEFAULT 0, month INTEGER NOT NULL DEFAULT 0,';

    for ($i=1; $i<=31; $i++) {
      $command .= 'd'.$i .' INTEGER NOT NULL DEFAULT 0, ';
    }

    $command .= 'PRIMARY KEY (unit_id, year, month))';

    return $command;
  }

  /**
   * SQL syntax to setup an hour table
   *
   * @param $table_name
   * @param $type
   * @return string
   */
  public static function createHourTable($table_name, $type) {
    $command = 'CREATE TABLE ' . 'bat_event_'.$table_name.'_hour_'.$type;
    $command .= ' (unit_id INTEGER NOT NULL DEFAULT 0, year INTEGER NOT NULL DEFAULT 0, month INTEGER NOT NULL DEFAULT 0, day INTEGER NOT NULL DEFAULT 0,';

    for ($i=0; $i<=23; $i++) {
      $command .= 'h'.$i .' INTEGER NOT NULL DEFAULT 0, ';
    }

    $command .= 'PRIMARY KEY (unit_id, year, month, day))';

    return $command;
  }

  /**
   * SQL syntax to setup a minute table
   *
   * @param $table_name
   * @param $type
   * @return string
   */
  public static function createMinuteTable($table_name, $type) {
    $command = 'CREATE TABLE ' . 'bat_event_'.$table_name.'_minute_'.$type;
    $command .= ' (unit_id INTEGER NOT NULL DEFAULT 0, year INTEGER NOT NULL DEFAULT 0, month INTEGER NOT NULL DEFAULT 0, day INTEGER NOT NULL DEFAULT 0, hour INTEGER NOT NULL DEFAULT 0,';

    for ($i=0; $i<=59; $i++) {
      if ($i <= 9) { $m='0' . $i; } else { $m = $i; }
      $command .= 'm'.$m .' INTEGER NOT NULL DEFAULT 0, ';
    }

    $command .= 'PRIMARY KEY (unit_id, year, month, day, hour))';

    return $command;
  }
}
