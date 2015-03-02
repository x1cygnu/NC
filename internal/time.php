<?php

$now = time();
function now() {
  return $GLOBALS['now'];
}

function timedecode($unixtime) {
  return strftime("%d %b %Y %T",$unixtime);
}

?>
