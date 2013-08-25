<?php

$U=new Table();
$U->sClass="menu";

$U->Insert(1,2,new Link("battlecalculator.php","Battlecalculator"));
if ($calcmenuselected=="BC") $U->SetClass(1,2,"menuselected");
$U->Insert(2,2,new Link("tt.php","Travel Time calculator"));
if ($calcmenuselected=="TT") $U->SetClass(2,2,"menuselected");

$U->Insert(1,1,"Game calculators");
$U->Join(1,1,2,1);
$U->SetClass(1,1,'title');

$H->Insert($U);
?>