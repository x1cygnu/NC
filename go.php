<?php
include_once("./internal/html/html.php");
include_once("./internal/html/table.php");
include_once("./internal/html/image.php");
include_once("./internal/html/form.php");
include_once("./internal/html/small.php");
include_once("./internal/html/as.php");
include_once("./internal/exception.php");
include_once("./internal/util.php");
include_once("./internal/args.php");
include_once("./internal/sql.php");
include_once("./internal/messages.php");

$UI = "";
session_start();

if (isset($_COOKIE['UI'])) {
  $tryui = $_COOKIE['UI'];
  if (in_array($tryui, array('uidefault')))
    $UI = $tryui;
  else
    $UI = 'uidefault';
} else {
  $UI = 'uidefault';
}
$H = new HTML("Northern Cross - Free Online Somewhat-Massive Multiplayer Game");
$H->addStyle($UI.'/basic.css');
$H->addStyle($UI.'/table.css');
$H->addStyle($UI.'/common.css');

include('./gopart.php');
echo $H;
?>
