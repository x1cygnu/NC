<?php

function str_or_empty(&$val) {
  if (isset($val))
    return $val;
  return '';
}

?>
