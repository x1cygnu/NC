<?php

chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/player.php");
include_once("internal/alliance.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);


ForceActivePlayer($sql, $H, "alliance.php");

ForceFrozen($sql, $H);

include("mobile/part/mainmenu.php");

$Cnt=player_count_buildings($sql, $_SESSION['PID'], "Embassy");
if ($Cnt==0)
{
    $H->Insert(new Error("Embassy required"));
    $H->Draw();
    CloseSQL($sql);
    die;
}


$tag=player_get_tag($sql, $_SESSION['PID']);

if ($tag=="") //no alliance
{
    $H->Insert(new Info("You are in no alliance yet"));
    $H->Draw();
    CloseSQL($sql);
    die;
}


$T=new Table();

$Ms=alliance_get_members($sql, $tag);
$i=0;
foreach ($Ms as $M)
{
    ++$i;
    $T->Insert(1,$i,new Link("post.php?pm={$M['AID']}","PM"));
    $T->Insert(2,$i,new Link("member.php?id={$M['PID']}","{$M['Nick']}"));
    $T->Insert(3,$i,"{$M['PCount']}/{$M['CultureLvl']}");

    $IdleT=EncodeNow()-$M['LastUpdate'];
    if ($IdleT<60) $Idle="{$IdleT}s";
    elseif ($IdleT<3600) {$IdleT=floor($IdleT/60); $Idle="{$IdleT}min";}
    elseif ($IdleT<3600*24) {$IdleT=floor($IdleT/3600); $Idle="{$IdleT}h";}
    else {$IdleT=floor($IdleT/(3600*24)); $Idle="{$IdleT}d";}
    
    $T->Insert(4,$i,$Idle);
}
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
