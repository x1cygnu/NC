<?php

$U=new Table();
$U->sClass="menu";

$U->Insert(1,1,new Link("battlecalculator.php","BC"));
$U->Insert(2,1,new Link("tt.php","TTC"));

$H->Insert($U);
?>