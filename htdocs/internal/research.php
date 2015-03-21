<?php
include_once('./internal/util.php');
include_once('./internal/level.php');

const NC_RESEARCH_SENSORY = 1;
const NC_RESEARCH_ENGINEERING = 2;
const NC_RESEARCH_MATHEMATICS = 3;
const NC_RESEARCH_PHYSICS = 4;
const NC_RESEARCH_WARP = 5;
const NC_RESEARCH_URBAN = 6;

$RESEARCH_SCIENCES = array(
    NC_RESEARCH_SENSORY,
    NC_RESEARCH_ENGINEERING,
    NC_RESEARCH_MATHEMATICS,
    NC_RESEARCH_PHYSICS,
    NC_RESEARCH_WARP,
    NC_RESEARCH_URBAN
    );
function RESEARCH_SCIENCES() {
  return $GLOBALS['RESEARCH_SCIENCES'];
}

function research_is_science($type) {
  return $type<100;
}

$RESEARCH_COST = array(
    NC_RESEARCH_SENSORY => 86400,
    NC_RESEARCH_ENGINEERING => 86400,
    NC_RESEARCH_MATHEMATICS => 86400,
    NC_RESEARCH_PHYSICS => 86400,
    NC_RESEARCH_WARP => 86400,
    NC_RESEARCH_URBAN => 86400
    );
function RESEARCH_COST() {
  return $GLOBALS['RESEARCH_COST'];
}


function research_get($sql, $pid, $type) {
  $val = $sql->NC_ResearchGet($pid, $type);
  if (empty($result))
    $val = 0;
  else
    $val = intval($result);
  $lvl = sci_lvl($type, $val);
  $result['Level'] = $lvl;
  $result['Progress'] = sci_towards_next_lvl($type, $val);
  $result['Max'] = sci_for_next_lvl($type, $lvl);
  $result['Remain'] = sci_next_lvl_remain($type, $lvl);
  $result['Points'] = $val;
  return $result;
}


function research_get_all_science($sql, $pid) {
  $results = $sql->get('NC_ResearchGetBelow',$pid, 100);
  $science = array();
  foreach ($results as $result) {
    $type = intval($result['Type']);
    $val = intval($result['Progress']);
    $lvl = sci_lvl($type, $val);
    $science[$type]['Level'] = $lvl;
    $science[$type]['Progress'] = sci_towards_next_lvl($type, $val);
    $science[$type]['Max'] = sci_for_next_lvl($type, $lvl);
    $science[$type]['Remain'] = sci_next_lvl_remain($type, $lvl);
    $science[$type]['Points'] = $val;
  }
  foreach (RESEARCH_SCIENCES() as $sciType) {
    if (!isset($science[$sciType])) {
      $science[$sciType]['Level'] = 0;
      $science[$sciType]['Progress'] = 0;
      $science[$type]['Max'] = sci_for_next_lvl($type, 0);
      $science[$type]['Remain'] = sci_next_lvl_remain($type, 0);
      $science[$type]['Points'] = 0;
    }
  }
  return $science;
}

?>
