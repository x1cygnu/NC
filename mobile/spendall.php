<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "spendall.php");

ForceFrozen($sql, $H);

$menuselected="Trade";
include("mobile/part/mainmenu.php");


$AT=planet_sum_PP($sql, $_SESSION['PID']);
$U=sprintf("%.2f@",$AT);
$T=new Table();
$T->sClass='legend';
$T->Insert(1,1,"You will get $U");
$T->Insert(1,2,new Link("trade.php?sa=1","OK"));
$T->Insert(2,2,new Link("planets.php","Cancel"));
$T->Join(1,1,2,1);
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
