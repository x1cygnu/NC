<?php
include_once('./internal/util.php');

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
    NC_RESEARCH_SENSORY => 24,
    NC_RESEARCH_ENGINEERING => 24,
    NC_RESEARCH_MATHEMATICS => 24,
    NC_RESEARCH_PHYSICS => 24,
    NC_RESEARCH_WARP => 24,
    NC_RESEARCH_URBAN => 24
    );
function RESEARCH_COST() {
  return $GLOBALS['RESEARCH_COST'];
}

function research_get_cost($type,$level) {
  return RESEARCH_COST()[$type];
}

function research_get($sql, $pid, $type) {
  $result = $sql->NC_ResearchGet($pid, $type);
  if (empty($result))
    $result = array('Level' => 0, 'Progress' => 0);
  makeint($result['Level']);
  makeint($result['Progress']);
  $result['Max'] = research_get_cost($type, $result['Level']);
  $result['Remain'] = $result['Max'] - $result['Progress'];
  return $result;
}


function research_get_all_science($sql, $pid) {
  $results = $sql->get('NC_ResearchGetBelow',$pid, 100);
  $science = array();
  foreach ($results as $result) {
    $type = intval($result['Type']);
    $level = intval($result['Level']);
    $progress = intval($result['Progress']);
    $max = research_get_cost($type, $level);
    $science[$type]['Level'] = $level;
    $science[$type]['Progress'] = $progress;
    $science[$type]['Max'] = $max;
    $science[$type]['Remain'] = $max-$progress;
  }
  foreach (RESEARCH_SCIENCES() as $sciType) {
    if (!isset($science[$sciType])) {
      $science[$sciType]['Level'] = 0;
      $science[$sciType]['Progress'] = 0;
      $science[$sciType]['Remain'] = $science[$sciType]['Max'] = research_get_cost($sciType, 0);
    }
  }
  return $science;
}

?>
