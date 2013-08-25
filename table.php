<?php

include_once("internal/html.php");

$H=new HTML();

$H->AddStyle("default.css");
$P=new Paragraph();
$U=clone $P;
$U->Insert("hakuna");
$H->Insert($P);
$T=new Table();
$T->sClass='block standard';
$T->SetCols(3);
$T->SetRows(3);
$U=clone $T;
$U->Insert(3,3,'U');
$T->Insert(3,3,'T');
$H->Insert($T);

$H->Draw();
?>