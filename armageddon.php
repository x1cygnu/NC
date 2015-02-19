<?php

set_time_limit(0);

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/armageddon.php");
include_once("internal/starsystem.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Armageddon";

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "armageddon.php");

if ($_SESSION['IsAdmin']!=1)
{
    $H->Insert(new Error("Only admin may terminate present round"));
    $H->Draw();
    die;
}

post("sendmail","integer");

post("restart","string");
if ($POST['restart']=="Restart")
{
    post("retyp","string");
    if ($POST['retyp']!="Yes I really want to destroy whole galaxy!")
	$H->Insert(new Error("Sentence not rewritten correctly"));
    else
    {
	post("round","string");
        post("time","string");
	round_restart($sql, $POST['round'], $POST['time'], $POST['sendmail']);
    }
}

get("fill","string");
if ($GET['fill']=="Fill")
{
    starsystem_fill($sql);
}

get('prand','string');
if ($GET['prand']=="Rand")
{
	planet_add_random($sql);
}

get("ring","string");
if ($GET['ring']=="Ring")
{
	echo "Ring create!";
	starsystem_ring_create($sql);
}

$menuselected="Armageddon";
include("part/mainmenu.php");

$T=new Table();
$T->Insert(1,1,"Terminate present round?");
$T->Insert(1,2,"New round name");
$T->Insert(2,2,new Input("text","round","","text"));
$T->Insert(1,3,"Launch time<br/>(i.e. 10 Nov 2006 10:00:00)");
$T->Insert(2,3,new Input("text","time","","text"));
$T->Insert(1,4,"Write: \"Yes I really want to destroy whole galaxy!\"");
$T->Insert(1,5,new Input('text','retyp','','text'));
$T->Insert(1,6,new Input("submit","restart","Restart","smbutton"));

$T->aRowClass[1]='title';
$T->aRowClass[6]='title';
$T->SetClass(1,2,'legend');
$T->SetClass(1,3,'legend');
$T->Join(1,1,2,1);
$T->Join(1,4,2,1);
$T->Join(1,5,2,1);
$T->Join(1,6,2,1);

$G=new Form("armageddon.php",false);
$G->Insert("Fill current ring with free planets");
$G->Insert(new Input("submit","fill","Fill","smbutton"));
$G->Insert(new Input("submit","ring","Ring","smbutton"));
$G->Insert(new Input("submit","prand","Rand","smbutton"));
$H->Insert($G);

$F=new Form("armageddon.php",true);
$F->Insert($T);
$F->Insert(new Input("checkbox","sendmail","1"));
$F->Insert("broadcast mail");
$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>
