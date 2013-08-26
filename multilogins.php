<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/log.php");
include_once("internal/account.php");

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

get("add","integer");
if (isset($GET['add']))
{
    multi_punish($sql, $GET['add'], "");
    $Name=account_get_name($sql, $GET['add']);
    $H->Insert(new Info("Player $Name marked multi"));
}
    
get("i","integer");
if (!isset($GET['i']))
    $GET['i']=1;

get("id","integer");
get("ipv","string");
if (isset($GET['id']))
{
    $Logs=multi_find($sql, $GET['id']);
    $Logcount=0;
}
elseif (isset($GET['ipv']))
{
    $Logs=multi_ip($sql, $GET['ipv']);
    $Logcount=0;
}
else
{
    $Logcount=multi_get_log_size($sql);
    $Logs=multi_get_log($sql, $GET['i']-1);
}

$T=new Table();
$T->Insert(1,1,"Multi-login database");
for ($u=1; $u<=$Logcount; $u=$u+100)
{
    $T->Insert(1,2,new Link("multilogins.php?i=$u","" . ceil($u/100)));
    $v=$u+50;
    $T->Insert(1,2,new Link("multilogins.php?i=$v","."));
}
$T->Insert(1,3,"Name");
$T->Insert(2,3,"ID");
$T->Insert(3,3,"IP");
$T->Insert(4,3,"Forward");
$T->Insert(5,3,"First noted");
$T->Insert(6,3,"Count");
$T->Insert(7,3,"Operations");
$T->Join(1,1,7,1);
$T->Join(1,2,7,1);
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->aRowClass[3]='legend';
$T->sClass='block';

$row=3;
foreach ($Logs as $Log)
{
    ++$row;
    $T->Insert(1,$row,new Link("multilogins.php?id=" . $Log['AID'], "" . $Log['Nick']));
    $T->Insert(2,$row,"" . $Log['AID']);
    $IP=$Log['IP0'].'.'.$Log['IP1'].'.'.$Log['IP2'].'.'.$Log['IP3'];
    $FIP=$Log['FIP0'].'.'.$Log['FIP1'].'.'.$Log['FIP2'].'.'.$Log['FIP3'];
    $T->Insert(3,$row,new Link("logins.php?ip=" . $IP, "" . $IP));
    $T->Insert(4,$row,$FIP);
    $T->Insert(5,$row,"" . DecodeTime($Log['Date']));
    $T->Insert(6,$row,"" . $Log['Count']);
    $T->Insert(7,$row,new Link("multilogins.php?add=" . $Log['AID'],"Punish"));
    $T->Insert(7,$row," ");
    $T->Insert(7,$row,new Link("multilogins.php?ipv=" . $Log['IP'],"View"));
    $T->Insert(7,$row," ");
    $T->Insert(7,$row,new Link("multiexceptions.php?aid={$Log['AID']}&ip={$Log['IP']}&fip={$Log['ForwardIP']}","Add"));
}

$H->Insert($T);

include("part/mainsubmenu.php");
$H->Draw();
?>
