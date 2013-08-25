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


$sql=&OpenSQL($H);

$W=$sql->query("SELECT count(DISTINCT P.SID) Wrum FROM NC_Planet P JOIN NC_Map M ON P.SID=M.SID "
	    ."WHERE M.Special=1");

$Wa=makeinteger($W[0]['Wrum']);

$sql->query("UPDATE NC_Alliance A SET A.TCP=$Wa-("
	. " SELECT count(DISTINCT Pl.SID) FROM NC_Planet Pl"
	. " JOIN NC_Map M ON M.SID=Pl.SID"
	. " LEFT JOIN NC_Player P ON P.PID=Pl.Owner"
	. " (P.TAG!=A.TAG OR P.TAG IS NULL) AND M.Special=1"
	. ")");

$H->Draw();
?>
