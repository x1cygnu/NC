<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/log.php");
include_once("internal/player.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Multi list";


$sql=&OpenSQL($H);

if (!$_SESSION['IsAdmin'])
{
    $H->Insert(new Error("Must be admin in order to see this page"));
    $H->Draw();
    die;
}

$menuselected="Multi";
include("part/mainmenu.php");
include("part/multi.php");

get("r","integer");
if (isset($GET['r']))
{
    $H->Insert(account_get_name_from_pid($sql, $GET['r']));
    $H->Br();
    $H->Insert(new Link("multilist.php?rr={$GET['r']}","Really force resign?"));
}

get("rr","integer");
if (isset($GET['rr']))
{
    player_force_resign($sql, $GET['rr']);
}


get("aid","integer");
if (isset($GET['aid']))
{
    account_set_multi($sql, $GET['aid'], 0);
}

get("ban","integer");
if (isset($GET['ban']))
{
	account_set_multi($sql, $GET['ban'], 9999);
}


$Logs=account_get_multies($sql);


$T=new Table();
$T->Insert(1,1,"Multies");
$T->Insert(1,2,"Name");
$T->Insert(2,2,"AID");
$T->Insert(3,2,"PID");
$T->Insert(4,2,"Multi lvl");
$T->Insert(5,2,"Operations");
$T->Join(1,1,5,1);
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->sClass='block';

$row=2;
foreach ($Logs as $Log)
{
    ++$row;
    $T->Insert(1,$row,new Link("logaddr.php?id=" . $Log['AID'], "" . $Log['Nick']));
    $T->Insert(2,$row,"" . $Log['AID']);
    $T->Insert(3,$row,"" . $Log['PID']);
    $T->Insert(4,$row,"" . $Log['Multi']);
    $T->Insert(5,$row,new Link("multilist.php?aid={$Log['AID']}","Clear"));
		$T->Insert(5,$row," ");
    $T->Insert(5,$row,new Link("multilist.php?r={$Log['PID']}","Resign"));
		$T->Insert(5,$row," ");
    $T->Insert(5,$row,new Link("multilist.php?ban={$Log['AID']}","Permban"));
}

$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
?>
