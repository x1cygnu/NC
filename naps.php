<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - NAPs";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "naps.php");

ForceFrozen($sql, $H);

$menuselected="NAP";
include("part/mainmenu.php");

$H->Draw();
CloseSQL($sql);
?>
