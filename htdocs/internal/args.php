<?php

$FieldMap = array(
    'login' => 'l',
    'showname' => 'shn',
    'password' => 'p',
    'password2' => 'p2',
    'email' => 'spam_here',

    'growth' => 'rg',
    'science' => 'rs',
    'culture' => 'rc',
    'production' => 'rp',
    'speed' => 'rsp',
    'attack' => 'ra',
    'defence' => 'rd',

    'news_from' => 'f',
    'news_count' => 'c',
    'news_fromtime' => 't',

    'submit_login' => 'sl',
    'submit_register' => 'sreg',
    'submit_player_create' => 'spc'
    );


function field($name) {
  global $FieldMap;
  $S = $FieldMap[$name];
  if (!$S)
    throw new Exception("Field $name not found");
  return $S;
}

function get($name, $type) {
  $f = field($name);
  if (!isset($_GET[$f]))
    return null;
  $v = $_GET[$f];
  if ($type == "string")
    return getString($v);
  settype($v,$type);
  return $v;
}

function post($name, $type) {
  $f = field($name);
  if (!isset($_POST[$f]))
    return null;
  $v = $_POST[$f];
  if ($type == "string")
    return getString($v);
  settype($v,$type);
  return $v;
}

function getSubmitted($name) {
  $s = field($name);
  return isset($_GET[$s]);
}

function postSubmitted($name) {
  $s = field($name);
  return isset($_POST[$s]);
}

function getString($s) {
  //TODO: Figure out if conversions are needed
  return $s;
}


?>
