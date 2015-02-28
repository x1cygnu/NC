<?php
include_once('./internal/util.php');

const RACE_GROWTH = 1;
const RACE_SCIENCE = 2;
const RACE_CULTURE = 3;
const RACE_PRODUCTION = 4;
const RACE_SPEED = 5;
const RACE_ATTACK = 6;
const RACE_DEFENCE = 7;

$race = array(
    RACE_GROWTH => array(
      -4 => 0.52,
      -3 => 0.62,
      -2 => 0.73,
      -1 => 0.86,
      0 => 1.00,
      +1 => 1.14,
      +2 => 1.25,
      +3 => 1.34,
      +4 => 1.42
      ),
    RACE_SCIENCE => array(
      -4 => 0.76,
      -3 => 0.81,
      -2 => 0.87,
      -1 => 0.93,
      0 => 1.00,
      +1 => 1.07,
      +2 => 1.13,
      +3 => 1.17,
      +4 => 1.21
      ),
    RACE_CULTURE => array(
      -4 => 0.73,
      -3 => 0.78,
      -2 => 0.85,
      -1 => 0.92,
      0 => 1.00,
      +1 => 1.08,
      +2 => 1.14,
      +3 => 1.19,
      +4 => 1.24
      ),
    RACE_PRODUCTION => array(
      -4 => 0.86,
      -3 => 0.89,
      -2 => 0.92,
      -1 => 0.96,
      0 => 1.00,
      +1 => 1.04,
      +2 => 1.07,
      +3 => 1.10,
      +4 => 1.12
      ),
    RACE_SPEED => array(
      -4 => 0.39,
      -3 => 0.51,
      -2 => 0.66,
      -1 => 0.82,
      0 => 1.00,
      +1 => 1.18,
      +2 => 1.32,
      +3 => 1.43,
      +4 => 1.54
      ),
    RACE_ATTACK => array(
      -4 => 0.66,
      -3 => 0.73,
      -2 => 0.81,
      -1 => 0.90,
      0 => 1.00,
      +1 => 1.10,
      +2 => 1.18,
      +3 => 1.24,
      +4 => 1.30
      ), 
    RACE_DEFENCE => array(
      -4 => 0.66,
      -3 => 0.73,
      -2 => 0.81,
      -1 => 0.90,
      0 => 1.00,
      +1 => 1.10,
      +2 => 1.18,
      +3 => 1.24,
      +4 => 1.30
      ) 
    )

function getRaceValue($raceType, $attribute) {
  return get_or_default($GLOBALS['race'][$raceType][$attribute],1.00);
}

function player_get_race($PID, $raceType) {
  $result = $sql->NC_RaceGet($PID, $raceType);
  if (isset($result))
    return getRaceValue($raceType, intval($result));
}

function player_set_race($PID, $raceType, $attribute) {
  $sql->NC_RaceSet($PID, $raceType, $attribute);
}


?>
