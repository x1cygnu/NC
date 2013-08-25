<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "empty.php");


ForceFrozen($sql, $H);

$H->Draw();
CloseSQL($sql);
?>
