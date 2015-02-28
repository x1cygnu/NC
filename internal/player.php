<?php
include_once('./internal/util.php');

function player_create($sql, $aid) {
  $pid = $sql->NC_PlayerCreate($aid, now());
  return $pid;
}

?>
