<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - minor calculators";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "minor.php");

$H->addJavaScriptFile('js/minor.js');

include("part/mainmenu.php");

$T=new Table();
$T->sClass='block';
$T->aRowClass[1]='title';
$T->Insert(1,1,"Calculator");
$T->Insert(2,1,"Arguments");
$T->Insert(3,1,"Result");
$T->Insert(4,1,"Fire button");

function newInput($id,$def="") {
    $i=new Input("text","",$def,"text number");
    $i->sId=$id;
    return $i;
}


$FIRE=new Input("button","","compute","smbutton");
$row=2;
$T->Insert(1,$row,"PL calculator");
$T->SetClass(1,$row,'legend');
$T->Insert(2,$row,"Initial PL:");
$T->Insert(2,$row,newInput("plInit"));
$T->Insert(2,$row," XP:");
$T->Insert(2,$row,newInput("plXP"));
$T->Get(3,$row)->sId='plResult';
$FIRE->onClick('computePl()');
$T->Insert(4,$row,$FIRE);

$H->Insert($T);

include("part/mainsubmenu.php");


$H->Draw();
CloseSQL($sql);
?>
