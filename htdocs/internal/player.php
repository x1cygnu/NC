<?php
include_once('./internal/util.php');
include_once('./internal/news.php');
include_once('./internal/starsystem.php');

function planet_change_owner($sql, $planet, $pid) {
  $sql->NC_PlanetChangeOwner($planet, $pid);
}

function player_create($sql, $aid) {
  $here = starsystem_spawn_planets_for_player($sql);
  $planet = $here['PID'];
  $pid = $sql->NC_PlayerCreate($aid, now(), $here['X'], $here['Y']);
  planet_change_owner($sql, $planet, $pid);
  news_insert($sql, News::create($pid, NEWS_WELCOME));
  return $pid;
}

function player_get_home($sql, $pid) {
  $result = $sql->NC_PlayerHome($pid);
  if (empty($result))
    throw new NCException("Home coordinates for player $pid not found");
  makeint($result['HomeX']);
  makeint($result['HomeY']);
  return $result;
}

function player_get_selected_research($sql, $pid) {
  return intval($sql->NC_PlayerGetSelectedResearch($pid));
}

?>
