<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->Insert(new Info("Nothing to write here yet"));
$sql=&OpenSQL($H);

include("part/mainsubmenu.php");


$H->Draw();
CloseSQL($sql);
?>
