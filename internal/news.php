<?php
define('NC_NEWS_WELCOME',1);

function news_insert($sql, $owner, $type) {
  return $sql->NC_NewsCreate($owner, $type, now());
}

function news_insert_timed($sql, $owner, $type, $time) {
  return $sql->NC_NewsCreate($owner, $type, $time);
}

?>
