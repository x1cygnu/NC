<?php
include_once("./internal/html/html.php");
include_once("./internal/html/table.php");
include_once("./internal/html/image.php");
include_once("./internal/html/form.php");
include_once("./internal/html/small.php");
include_once("./internal/html/frame.php");
include_once("./internal/html/link.php");
include_once("./internal/html/progress.php");
include_once("./internal/html/as.php");
include_once("./internal/exception.php");
include_once("./internal/util.php");
include_once("./internal/time.php");
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
$H->addStyleFile($UI.'/basic.css');
$H->addStyleFile($UI.'/table.css');
$H->addStyleFile($UI.'/common.css');

include('./gopart.php');
echo $H;
?>
