<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross";

$sql=&OpenSQL($H);

$pt=planet_get_ext_types($sql);
$T=new Table();
$T->Insert(1,1,'class');
$T->Insert(2,1,'Modifiers');
$T->Insert(2,2,'Growth');
$T->Insert(3,2,'Science');
$T->Insert(4,2,'Culture');
$T->Insert(5,2,'Production');
$T->Insert(6,2,'Toxic');
$T->Insert(7,1,'Building<br>base cost');
$T->Insert(8,1,'Starbase');
$T->Insert(8,2,'Attack');
$T->Insert(9,2,'Defense');
$T->Insert(10,1,'Culture<br>slot');
$T->Insert(11,1,'Technology');
$T->Join(1,1,1,2);
$T->Join(2,1,4,1);
$T->Join(7,1,1,2);
$T->Join(8,1,2,1);
$T->Join(10,1,1,2);
$T->Join(11,1,1,2);

$T->aRowClass[1]='legend';
$T->aRowClass[2]='legend';
$row=3;
foreach ($pt as $p) {
    $T->Insert(1,$row,(($row-1)/2) . ':' . $p['TypeName']);
    $T->Insert(2,$row,$p['Growth']);
    $T->Insert(3,$row,$p['Science']);
    $T->Insert(4,$row,$p['Culture']);
    $T->Insert(5,$row,$p['Production']);
    $T->Insert(6,$row,$p['ToxicStability']);
    $T->Insert(7,$row,$p['BaseCost']);
    $T->Insert(8,$row,$p['Attack']);
    $T->Insert(9,$row,$p['Defense']);
    $T->Insert(10,$row,($p['CultureSlot']>0?'Yes':'No'));
    $T->Insert(11,$row,'' . $p['Name']);
    $T->Insert(1,$row+1,'' . $p['Description']);
    $T->Join(1,$row+1,11,1);
    $T->aRowClass[$row]='sublegend';
    $row+=2;
}

$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
