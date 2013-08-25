<?php

include_once("internal/html.php");

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$I=new Image("IMG/s1.gif");

$I->onMouseOver("this.src='IMG/s13.gif'");
$I->onMouseOut("this.src='IMG/s1.gif'");

$H->Insert($I);


$H->Draw();
?>
