<?php
const NEWS_WELCOME = 1;

class News {
  public $NID = null;
  public $owner;
  public $type;
  public $showtime;
  public $item = array();

  static public function createTimed($owner, $type, $time) {
    $n = new News();
    $n->owner = $owner;
    $n->type = $type;
    $n->showtime = $time;
    return $n;
  }
  static public function create($owner, $type) {
    return self::createTimed($owner, $type, now());
  }
};

function news_insert($sql, News $n) {
  $n->NID = $sql->NC_NewsCreate($n->owner, $n->type, $n->showtime);
  foreach ($n->item as $key=>$value) {
    $sql->NC_NewsSetItem($n->NID, $key, $value);
  }
}

function news_get($sql, $owner, $from, $count) {
  $n = null;
  $result = array();
  $data = $sql->NC_NewsGet($owner, now(), $from, $count);
  foreach ($data as $row) {
    if (isset($n) and $n->NID != $row['NID']) {
      $n = new News();
      $n->NID = $row['NID'];
      $n->owner = $row['Owner'];
      $n->type = $row['NewsType'];
      $n->showtime = $row['ShowTime'];
      $result[] = $n;
    }
    if (isset(row['ItemType']))
      $n->item[intval(row['ItemType'])] = row['ItemValue'];
  }
  return $result;
}

function news_get_all($sql, $owner) {
  return news_get($sql, now(), 0, PHP_INT_MAX);
}


?>
