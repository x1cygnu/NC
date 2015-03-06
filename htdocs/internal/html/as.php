<?php

function asRadio($obj, $key, $value, $selected=false) {
  $obj->setAttribute('data-key',$key);
  $obj->setAttribute('data-value',''.$value);
  if ($selected)
    $obj->setAttribute('data-selected','1');
  $obj->onClick('asRadioClick(this)');
}

?>
