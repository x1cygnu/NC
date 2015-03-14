<?php
include_once('./internal/util.php');

const NC_RESEARCH_SENSORY = 1;
const NC_RESEARCH_ENGINEERING = 2;
const NC_RESEARCH_MATHEMATICS = 3;
const NC_RESEARCH_PHYSICS = 4;
const NC_RESEARCH_WARP = 5;
const NC_RESEARCH_URBAN = 6;

$RESEARCH_COST = array(
    NC_RESEARCH_SENSORY => 24,
    NC_RESEARCH_ENGINEERING => 24,
    NC_RESEARCH_MATHEMATICS => 24,
    NC_RESEARCH_PHYSICS => 24,
    NC_RESEARCH_WARP => 24,
    NC_RESEARCH_URBAN => 24
    );

function research_get_cost($type,$level) {
  global $RESEARCH_COST;
  return $RESEARCH_COST[$type];
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


?>
