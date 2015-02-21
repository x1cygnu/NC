<?php
include_once("internal/header.php");
$H = nc_header();
$savedPlayerName = "";
if (isset($_COOKIE['pname']))
  $savedPlayerName = $_COOKIE['pname'];
include($UI.'/index.php');
echo $H;
?>
