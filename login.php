<?php
include_once("internal/header.php");
include_once("internal/account.php");

$H = nc_header();
if (postSubmitted('login')) {
  $sql = openSQL();
  $login = post('login','string');
  $password = post('password','string');
  $AID = account_login($sql, $login, $password);
  if ($AID>0) {
    info("Account created\n");
    include($UI.'/login.php');
  } else
    include($UI.'/index.php');
  $sql->close();
} else {
  $savedPlayerName = "";
  if (isset($_COOKIE['pname']))
    $savedPlayerName = $_COOKIE['pname'];
  include($UI.'/index.php');
}
echo $H;
?>
