<?php

function asRadio($obj, $key, $value, $selected=false) {
  $obj->setAttribute('data-key',$key);
  $obj->setAttribute('data-value',''.$value);
  if ($selected)
    $obj->setAttribute('data-selected','1');
  $obj->onClick('asRadioClick(this)');
}

function asLink($obj, $target, $history=true) {
  if ($history)
    $func = 'assign';
  else
    $func = 'replace';
  $obj->onClick("window.location.$func(\"$target\")");
}

?>
