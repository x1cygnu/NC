<?php
define('NC_NEWS_WELCOME',1);

function news_insert($sql, $owner, $type) {
  return $sql->NC_NewsCreate($owner, $type, now());
}

function news_insert_timed($sql, $owner, $type, $time) {
  return $sql->NC_NewsCreate($owner, $type, $time);
}

function news_get($sql, $owner, $from, $count) {
  return $sql->NC_NewsGet($owner, now(), $from, $count);
}

function news_get_all($sql, $owner) {
  return $sql->NC_NewsGet($owner, now(), 0, PHP_INT_MAX);
}

?>
