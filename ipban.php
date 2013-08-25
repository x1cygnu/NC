<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/log.php");
include_once("internal/player.php");
include_once("internal/ip.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Global IP ban list";


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

get("add","string");
if (isset($GET['add']))
{
	ipban_add($sql,$GET['add']);
}

get("del","string");
if (isset($GET['del']))
{
	ipban_remove($sql,$GET['del']);
}


$IPs=ipban_list($sql);

$M=new Table();
$M->sClass='block';
$M->Insert(1,1,"Add an IP");
$M->Insert(2,2,"IP (0 for any)");
$M->Join(1,1,2,1);
$M->aRowClass[1]='title';
$M->aRowClass[2]='legend';
$M->Insert(2,3,new Input("text","add",'',"text"));
$M->Insert(1,4,new Input("submit","","add","smbutton"));
$M->Join(1,4,2,1);
$F=new Form("ipban.php",false);
$F->Insert($M);
$H->Insert($F);

$T=new Table();
$T->Insert(1,1,"Globally blocked IPs");
$T->Insert(1,2,"IP");
$T->Insert(2,2,"Operation");
$T->Join(1,1,2,1);
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->sClass='block';

$row=2;
foreach ($IPs as $IP)
{
    ++$row;
		$T->Insert(1,$row,$IP);
		$T->Insert(2,$row,new Link('ipban.php?del='.$IP,"Remove"));
}

$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
?>
