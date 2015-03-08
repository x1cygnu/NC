<?php
include_once('./internal/util.php');
include_once('./internal/news.php');
include_once('./internal/starsystem.php');

function planet_change_owner($sql, $planet, $pid) {
  $sql->NC_PlanetChangeOwner($planet, $pid);
}

function player_create($sql, $aid) {
  $pid = $sql->NC_PlayerCreate($aid, now());
  $planet = starsystem_spawn_planets_for_player($sql);
  planet_change_owner($sql, $planet, $pid);
  news_insert($sql, News::create($pid, NEWS_WELCOME));
  return $pid;
}

?>
