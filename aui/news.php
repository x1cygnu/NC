<?php
if (!isset($_SESSION['AID']))
  throw new NCAIDMissException('You are not logged in');
$AID = $_SESSION['AID'];
if (!isset($_SESSION['PID']))
  throw new NCPIDMissException('You are an inactive player');
$PID = $_SESSION['PID'];

include_once('./internal/news.php');

$from = get('news_from','integer');
$count = get('news_count','integer');
$timelimit = get('news_fromtime','integer');

$sql = openSQL();
/*
if (isset($from) and isset($count))
  $news = news_get($sql, $PID, $from, $count);
elseif (isset($timelimit))
  $news = news_get_timed($sql, $PID, $timelimit);
else
  $news = news_get_timed($sql, $PID, $_SESSION['LastLogin']);
  */
$news = news_get_all($sql, $PID);
$sql->close();

?>
