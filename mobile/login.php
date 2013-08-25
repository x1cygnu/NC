<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/account.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/race.php");
include_once("internal/armageddon.php");

session_start();

global $POST;
$POST=array();

post("login","string");
post("password","string");

$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

if (!account_login($sql, $POST['login'], $POST['password']))
{
    $H->Insert(new Error("Player not found, not registered or incorrect password"));
    $H->Script("function refr(){window.location='index.php';}\n");
    $H->Script("setTimeout(refr,3000)");
}
else
{
    include("mobile/part/mainmenu.php");
    $H->Insert(new Info("Login successful"));
    if ($_SESSION['PID']!=0)
    {
	$H->Insert("You will be redirected to your news screen");
	$H->Script("function refr(){window.location='news.php';}\n");
	$H->Script("setTimeout(refr,500)");
    }
    else
    {
	if (round_present($sql) or $_SESSION['IsAdmin'])
	{
	$H->Insert("You have no civilisation under your control");
	}
	else
	$H->Insert("Round has not started yet!");
    }
}

$H->Draw();
?>
