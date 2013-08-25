<?php

chdir('..');
include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/config.php");
include_once("internal/security/validator.php");
include_once("internal/planet.php");
include_once("internal/starsystem.php");
include_once("internal/fleet.php");

session_start();

global $GET;
$GET=array();



$H = new HTML();
$H->AddStyle("default.css");


$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "launch.php");

ForceFrozen($sql, $H);

include("mobile/part/mainmenu.php");

get("id","integer");
if (!exists($GET['id']))
{
    post("from","integer");

    post("system","integer");
    post("ring","integer");
    $to=planet_get_id($sql,$POST['system'],$POST['ring']);
    $details=array();
    $Answer=fleet_simulate($sql,$_SESSION['PID'],$POST['from'],$to,$details);
    if (gettype($Answer)=="string")
	$H->Insert(new Error("$Answer"));
    else
    {
	$H->Insert("Launching at " . DecodeTime(EncodeNow()));
	$H->Insert(new Info("ETA is " . DecodeTime($Answer)));
    }
    
    $GET['id']=$POST['from'];
}

$P=planet_get_all($sql,$GET['id']);

if ($P['FleetOwner']!=$_SESSION['PID'])
{
    $H->Insert(new Error("You command no fleet there"));
    $H->Draw();
    die;
}

$F=new Form("fleet.php",true);
$F->sName=$F->sId="fleet";

$T=new Table();
$T->Insert(1,1,"{$P['Name']} {$P['Ring']}");


$T->Insert(1,4,"Int");
$B=new Input("text","int","0","sh");
$B->sId='ints';
$T->Insert(2,4,$B);
$T->Insert(3,4,"{$P['Int']}");

$T->Join(1,1,3,1);
$T->Join(1,2,3,1);

$T->Insert(1,5,"Fr");
$B=new Input("text","fr","0","sh");
$B->sId='fr';
$T->Insert(2,5,$B);
$T->Insert(3,5,"{$P['Fr']}");

$T->Insert(1,6,"Bs");
$B=new Input("text","bs","0","sh");
$B->sId='bs';
$T->Insert(2,6,$B);
$T->Insert(3,6,"{$P['Bs']}");

$T->Insert(1,7,"CS");
$B=new Input("text","cs","0","sh");
$B->sId='cs';
$T->Insert(2,7,$B);
$T->Insert(3,7,"{$P['CS']}");

$T->Insert(1,8,"Tr");
$B=new Input("text","tr","0","sh");
$B->sId='tr';
$T->Insert(2,8,$B);
$T->Insert(3,8,"{$P['Tr']}");

$F->Insert($T);

$F->Insert("Orbit");
$F->Insert(new Input("text","ring","","sh"));
$F->Br();
$F->Insert("Simulation");
$F->Insert(new Input("checkbox","sim","1"));

$TP=new Table();
$TP->SetCols(3);
$TP->Insert(1,1,"Target (with your planets)");
$TP->Join(1,1,3,1);
$TP->aRowClass[1]='t';

$TU=new Table();
$TU->SetCols(3);
$TU->Insert(1,1,"Target (rest)");
$TU->Join(1,1,3,1);
$TU->aRowClass[1]='t';

$ip=1;
$iu=1;
$L=starsystem_bio($sql,$_SESSION['PID'],true);
foreach ($L as $S)
{
    if ($S['YPC']>0)
    {
	++$ip;
	$TP->Insert(1,$ip,$S['Name']);
	$TP->Insert(2,$ip,"{$S['X']}/{$S['Y']}");
	$TP->Insert(3,$ip,new Input("radio","system","{$S['SID']}"));
    }
    else
    {
	++$iu;
	$TU->Insert(1,$iu,$S['Name']);
	$TU->Insert(2,$iu,"{$S['X']}/{$S['Y']}");
	$TU->Insert(3,$iu,new Input("radio","system","{$S['SID']}"));
    }
}



$F->Br();
$F->Insert(new Input("submit","send","Launch!","smbutton"));
$F->Insert($TP);
$F->Insert(new Input("submit","send","Launch!","smbutton"));
$F->Insert($TU);
$F->Insert(new Input("hidden","from",$GET['id']));

$F->Insert(new Input("submit","send","Launch!","smbutton"));



$F->onSubmit("if (document.fleet.sim.checked){document.fleet.action='launch.php';}");

if (planet_gateway_available($sql, $GET['id'], $_SESSION['PID']))
{
    $T=new Table();
    $T->Insert(1,1,"Gateway jump");
    $T->aRowClass[1]='title';
    $T->Insert(1,2,"Gate address");
    $T->SetClass(1,2,'legend');
    $T->Insert(2,2,new Input("text","GateAddr","","text"));
    $T->Insert(1,3,new Input("submit","send","Jump!","smbutton"));
    $T->Insert(1,3,"<br>");
    $T->Insert(1,3,"Warning: no confirmation! Constant travel time 1h");
    $T->aRowClass[3]='legend';
    $T->Join(1,1,2,1);
    $T->Join(1,3,2,1);
    $F->Insert($T);
}

$H->Insert($F);
$H->Draw();
CloseSQL($sql);
?>

