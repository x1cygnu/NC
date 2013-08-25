<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/race.php");
include_once("internal/player.php");
include_once("internal/armageddon.php");

session_start();

global $POST;
$POST=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Creating race";

$sql=&OpenSQL($H);

ForceLogin($sql, $H, "race.php");

if (!(round_present($sql) or $_SESSION['IsAdmin']))
{
    $H->Insert(new Error("Round has not started yet. You cannot create a new race"));
    $H->Draw();
    die;
}

ForceFrozen($sql, $H);

post("Growth","integer");
post("Science","integer");
post("Culture","integer");
post("Production","integer");
post("Speed","integer");
post("Attack","integer");
post("Defence","integer");


if (!exists($POST['Growth']) or !exists($POST['Science']) or
    !exists($POST['Culture']) or !exists($POST['Production']) or
    !exists($POST['Speed']) or !exists($POST['Attack']) or
    !exists($POST['Defence']))
{
    $H->Insert(new Error("Some race attributes are missing"));
    include("part/race.php");
}
elseif ($POST['Growth']+$POST['Science']+$POST['Culture']+$POST['Production']+$POST['Speed']+$POST['Attack']+$POST['Defence']!=0)
{
    $H->Insert(new Error("Race modifiers must sum up to 0"));
    include("part/race.php");
}
elseif (4<max(abs($POST['Growth']),abs($POST['Science']),abs($POST['Culture']),abs($POST['Production']),abs($POST['Speed']),abs($POST['Attack']),abs($POST['Defence'])))
{
    $H->Insert(new Error("Race modifiers must between -4 and +4"));
    include("part/race.php");
}
elseif ($_SESSION['PID']!=0)
{
    $H->Insert(new Error("You already have a civilisation under your control!"));
    include("part/mainmenu.php");
}	//everything ok
else
{
    if (player_create($sql, $_SESSION['AID'], $POST['Growth'], $POST['Science'],$POST['Culture'],$POST['Production'],$POST['Speed'],$POST['Attack'],$POST['Defence'])!=0)
    {    
	include("part/mainmenu.php");
	$H->Insert(new Info("New civilisation emerged from the void"));
	$H->Insert("You may now begin playing Northern Cross!");
    }
    else
    {
	$H->Insert($GLOBALS['Error']->Report());
	include("part/mainsubmenu.php");
    }
}
$H->Draw();
CloseSQL($sql);
?>
