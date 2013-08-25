<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/progress.php");
include_once("internal/player.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "planets.php");
ForceFrozen($sql, $H);

include("mobile/part/mainmenu.php");

$L=planet_list($sql, $_SESSION['PID']);

$T=new Table();
$T->sClass="block";
$T->Insert(1,1,"Name");
$T->Insert(2,1,"P");
$T->Insert(3,1,"Tx");
$T->Insert(4,1,"F");
$T->Insert(5,1,"F");
$T->Insert(6,1,"C");
$T->Insert(7,1,"L");
$T->Insert(8,1,"R");

$T->Insert(9,1,"PP");
$H->Insert(new Link("spendall.php"," [spend all] "));
$T->aRowClass[1]='title';

$i=1;
$row=0;
foreach ($L as $planet)
{
    ++$i;
    $T->Insert(1,$i,new Link("planet.php?id=$row","{$planet['Name']} {$planet['Ring']}"));
    $T->Insert(2,$i,"{$planet['Population']}");
    $PP=floor($planet['PP']);
    $STx=floor($planet['STx']/1000);
    $T->Insert(3,$i,"$STx");
    $T->Insert(4,$i,"{$planet['Farm']}");
    $T->Insert(5,$i,"{$planet['Factory']}");
    $T->Insert(6,$i,"{$planet['Cybernet']}");
    $T->Insert(7,$i,"{$planet['Lab']}");
    $T->Insert(8,$i,"{$planet['Refinery']}");
    $T->Insert(9,$i,"$PP");
    
    if ($planet['Owner']!=$planet['FleetOwner'] and $planet['FleetOwner']!=0)
	$T->aRowClass[$i]="r";
    ++$row;
}

$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
