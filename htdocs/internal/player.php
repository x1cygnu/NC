<?php
include_once('./internal/util.php');
include_once('./internal/news.php');
include_once('./internal/starsystem.php');

function player_create($sql, $aid) {
  $pid = $sql->NC_PlayerCreate($aid, now());
  $planet = starsystem_spawn_planets_for_player($sql);
  news_insert($sql, News::create($pid, NEWS_WELCOME));
  return $pid;
}

?>
