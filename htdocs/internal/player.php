<?php
include_once('./internal/util.php');
include_once('./internal/news.php');

function player_create($sql, $aid) {
  $pid = $sql->NC_PlayerCreate($aid, now());
  news_insert($sql, News::create($pid, NEWS_WELCOME));
  return $pid;
}

?>
