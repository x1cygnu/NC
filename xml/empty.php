<?php

chdir('..');
include_once("internal/xml.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");

session_start();

global $GET;
$GET=array();

$X = new XML("NC");

$sql=&OpenSQL();

if (!CheckActivePlayerXML($sql, $X)) {$X->Draw(); die;}
if (!CheckFrozenXML($sql, $X)) {$X->Draw(); die;}


$X->Draw();
CloseSQL($sql);
?>
