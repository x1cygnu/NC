<?php

const PLANET_CORONA = 0;
const PLANET_GAIA = 1;

function planet_create($sql, $SID, $type) {
  return $sql->NC_PlanetCreate($SID, $type);
}

?>
