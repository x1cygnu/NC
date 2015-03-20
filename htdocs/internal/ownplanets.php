<?php

function own_planets_get($sql, $PID) {
  return $sql->get('NC_OwnedPlanetsGet',$PID);
}

?>
