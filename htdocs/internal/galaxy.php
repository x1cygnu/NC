<?php
include_once('./internal/starsystem.php');

function galaxy_get($sql, $x, $y, $range) {
  return $sql->get('NC_GalaxyGetRange',$x, $y, $range);
}

function galaxy_get_background($sql, $x, $y, $range) {
  $x1=$x-$range;
  $y1=$y-$range;
  $x2=$x+$range;
  $y2=$y+$range;
  return $sql->get('NC_GalaxyGetBackground',$x1,$y1,$x2,$y2);
}

?>
