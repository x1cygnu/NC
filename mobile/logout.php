<?php
chdir('..');

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/account.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$sql=&OpenSQL($H);

ForcePlayer($sql, $H, "news.php");

$menuselected="Logout";


account_logout($sql);

$H->Insert(new Info("You have been logged out"));
$H->Insert(new Link("index.php","Main page"));
$H->Draw();
CloseSQL($sql);
?>
