<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/fleet.php");
include_once("internal/planet.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Fleet";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "fleet.php");

$menuselected="Fleet";
$H->AddStyle("detail.css");
$H->AddStyle("fleet.css");


ForceFrozen($sql, $H);

include("mobile/part/mainmenu.php");

post("send","string");
if ($POST['send']=="Launch!")	//launching fleet
{
    post("confirm","integer");
    post("int","integer");
    post("fr","integer");
    post("bs","integer");
    post("tr","integer");
    post("cs","integer");
    post("system","integer");
    post("ring","integer");
    post("from","integer");
    $to=planet_get_id($sql,$POST['system'],$POST['ring']);
    if ($to==0)
	$H->Insert(new Error("Planet not found"));
    else
    {
    if ($POST['confirm']==0 and $_SESSION['ConfirmLaunch']==1)
    {
    $H->Insert(new Info("Confirmation required"));
    
    $T=new Table();
        $T->Insert(1,1,"Int");
	    $T->Insert(2,1,"{$POST['int']}");
        $T->Insert(1,2,"Fr");
	    $T->Insert(2,2,"{$POST['fr']}");
        $T->Insert(1,3,"Bs");
	    $T->Insert(2,3,"{$POST['bs']}");
        $T->Insert(1,4,"Tr");
	    $T->Insert(2,4,"{$POST['tr']}");
        $T->Insert(1,5,"CS");
	    $T->Insert(2,5,"{$POST['cs']}");

    $T->Insert(1,6,"To");
	    $T->Insert(2,6,planet_get_name($sql, $to) . "");
	$T->Insert(1,7,"Own");
	    $plto=planet_get_nick($sql, $to);
	    $T->Insert(2,7,"[{$plto['TAG']}] {$plto['Nick']}");
    
    $T->Insert(1,8,"TT");
    $details=array();
    $ETA=fleet_simulate($sql, $_SESSION['PID'], $POST['from'], $to, $details);
    $TTs=time_period($details['TT']);
        $T->Insert(2,8,$TTs . "");
    $T->Insert(1,9,"ETA");
        $T->Insert(2,9,DecodeTime($ETA));
    
    $F=new Form("fleet.php",true);
    $POST['confirm']="1";
    unset($POST['send']);
    foreach ($POST as $Key => $Val)
	$F->Insert(new Input("hidden","$Key","$Val"));
    $F->Insert(new Input("submit","send","Launch!","smbutton"));
    $H->Insert($F);
    $H->Insert($T);

    }
    else
    {
    $Answer=fleet_send($sql,$_SESSION['PID'],$POST['int'],$POST['fr'],$POST['bs'],$POST['cs'],$POST['tr'],$POST['from'],$to);
    if ($Answer === true)
	$H->Insert(new Info("Ships succesfully launched"));
    elseif ($Answer === false)
	$H->Insert(new Error("Unknown internal error"));
    else
	$H->Insert(new Error($Answer));
    }
    }
}

if ($POST['send']=="Jump!")
{
    post("GateAddr","string");
    post("from","integer");
    if (planet_gateway_available($sql, $POST['from'], $_SESSION['PID']))
    {
        post("int","integer");
	post("fr","integer");
        post("bs","integer");
	post("tr","integer");
        post("cs","integer");

	$to=makeinteger(planet_gateway_find(&$sql, $POST['GateAddr']));
	if ($to==0)
	    $to=$POST['from'];
        $Answer=fleet_send($sql,$_SESSION['PID'],$POST['int'],$POST['fr'],$POST['bs'],$POST['cs'],$POST['tr'],$POST['from'],-$to);
	if ($Answer === true)
	    $H->Insert(new Info("Ships succesfully launched"));
	elseif ($Answer === false)
	    $H->Insert(new Error("Unknown internal error"));
	else
	    $H->Insert(new Error($Answer));
    }
}

$A=fleet_get_stationary($sql, $_SESSION['PID']);
$B=fleet_get_intransit($sql, $_SESSION['PID']);
if (count($A)+count($B)==0)
{
    $H->Insert(new Error("You have no fleet"));
    $H->Draw();
    die;
}

$T=new Table();

$T->Insert(1,1,"Location");
$T->Insert(2,1,"Int");
$T->Insert(3,1,"Fr");
$T->Insert(4,1,"Bs");
$T->Insert(5,1,"CS");
$T->Insert(6,1,"Tr");

$i=1;
foreach ($A as $F)
{
    ++$i;
    $T->Insert(1,$i,new Link("detail.php?id={$F['SID']}","{$F['Name']} {$F['Ring']}"));
    $T->Insert(2,$i,"{$F['Int']}");
    $T->Insert(3,$i,"{$F['Fr']}");
    $T->Insert(4,$i,"{$F['Bs']}");
    $T->Insert(5,$i,"{$F['CS']}");
    $T->Insert(6,$i,"{$F['Tr']}");
    $T->Insert(7,$i,new Link("launch.php?id={$F['PLID']}","<b>Launch</b>"));
    if ($F['Siege']==1) $T->aRowClass[$i]='siegedplanet';
}

$H->Insert($T);

$T=new Table();

$T->SetCols(7);
$T->Insert(1,1,"Fleets in transit");
$T->Join(1,1,7,1);
$T->aRowClass[1]='t';


$T->Insert(1,2,"Target");
$T->Insert(2,2,"Int");
$T->Insert(3,2,"Fr");
$T->Insert(4,2,"Bs");
$T->Insert(5,2,"CS");
$T->Insert(6,2,"Tr");
$T->Insert(7,2,"ETA");


$i=2;
foreach ($B as $F)
{
    ++$i;
    $T->Insert(1,$i,"{$F['Name']} {$F['Ring']}");
    $T->Insert(2,$i,"{$F['Int']}");
    $T->Insert(3,$i,"{$F['Fr']}");
    $T->Insert(4,$i,"{$F['Bs']}");
    $T->Insert(5,$i,"{$F['CS']}");
    $T->Insert(6,$i,"{$F['Tr']}");
    $T->Insert(7,$i,DecodeTime($F['ETA']));
}

$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
