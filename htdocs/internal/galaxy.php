<?php
include_once('./internal/starsystem.php');

function galaxy_get($sql, $x, $y, $range) {
  return $sql->get('NC_GalaxyGetRange',$x, $y, $range);
}

?>
