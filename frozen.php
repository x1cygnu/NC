<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

session_start();


$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Freezing";

if ($_SESSION['IsAdmin']!=1)
{
    $H->Insert(new Error("Only admin may freeze the game"));
    $H->Draw();
    die;
}


$menuselected="Frozen";

$sql=&OpenSQL($H);

post("freeze","string");
if ($POST['freeze']=="Freeze")
{
    $H->Insert("Freezing the game");
    post("ftime","string");
    post("ttime","string");
    round_freeze(&$sql, $POST['ftime'], $POST['ttime']);
}

include("part/mainmenu.php");

$T=new Table();
$T->Insert(1,1,"Freeze the game?<br/>(use format: 10 Nov 2006 10:00:00)");
$T->Insert(1,2,"From");
$T->Insert(2,2,new Input("text","ftime",FullDecodeTime(EncodeNow(),0),"text"));
$T->Insert(1,3,"To");
$T->Insert(2,3,new Input("text","ttime",FullDecodeTime(EncodeNow(),0),"text"));
$T->Insert(1,4,new Input("submit","freeze","Freeze","smbutton"));

$T->aRowClass[1]='title';
$T->aRowClass[4]='title';
$T->SetClass(1,2,'legend');
$T->SetClass(1,3,'legend');
$T->Join(1,1,2,1);
$T->Join(1,4,2,1);

$F=new Form("frozen.php",true);
$F->Insert($T);
$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>
