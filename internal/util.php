<?php

function get_or_default(&$val, $default) {
  if (isset($val))
    return $val;
  return $default;
}

function str_or_empty(&$val) {
  if (isset($val))
    return $val;
  return '';
}

$now = time();
function now() {
  return $_GLOBALS['now'];
}

?>
