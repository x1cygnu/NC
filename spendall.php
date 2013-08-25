<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/player.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Spend all";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "spendall.php");
ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);

include("part/sitpid.php");

$menuselected="Trade";
include("part/mainmenu.php");


$AT=planet_sum_PP($sql, $MainPID);
$U=sprintf("%.2f@",$AT);
$T=new Table();
$T->sClass='legend';
$T->Insert(1,1,"You will get $U");
$T->Insert(1,2,new Link("trade.php?sa=1&orderid=".($_SESSION['PostCode']+1) . $sitAddition,"OK"));
if ($MainPID==$_SESSION['PID'])
    $T->Insert(2,2,new Link("planets.php","Cancel"));
else
    $T->Insert(2,2,new Link("planets.php?sit=1","Cancel"));
$T->Join(1,1,2,1);
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
