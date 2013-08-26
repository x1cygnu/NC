<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");
include_once("internal/fleet.php");
include_once("internal/planet.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");

$H->sTitle="Northern Cross - Fleet";

$sql=&OpenSQL($H);

ForceActivePlayer($sql, $H, "fleet.php");

$menuselected="Fleet";
$H->AddStyle("detail.css");
$H->AddStyle("fleet.css");


ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);
include("part/sitpid.php");

include("part/mainmenu.php");

if (PostControl(true))
	post("send","string");
	if ($POST['send']=="Launch!")	//launching fleet
{
	post("confirm","integer");
	post("vpr","integer");
	post("int","integer");
	post("fr","integer");
	post("bs","integer");
	post("drn","integer");
	post("tr","integer");
	post("cs","integer");
	post("system","integer");
	post("mis","integer");
	post("planet","integer");
	post("ring","integer");
	post("from","integer");
	if ($POST['planet']>0)
		$to=$POST['planet'];
	else
		$to=planet_get_id($sql,$POST['system'],$POST['ring']);
	if ($to==0)
		$H->Insert(new Error("Planet not found"));
	else if ($POST['mis']<1 || $POST['mis']>4)
		$H->Insert(new Error("Invalid mission parameters"));
	else
	{
		$plowner=planet_get_owner($sql, $to);
		$sittingLaunchAbort=false;
		if ($MainPID!=$_SESSION['PID'] and $MainPID!=$plowner)
		{
			$H->Insert(new Error("You cannot send sitted fleet to a planet not owned by the sitted player"));
			$sittingLaunchAbort=true;
		}

		if ($POST['confirm']==0 and $_SESSION['ConfirmLaunch']==1)
		{
			$H->Insert(new Info("Confirmation required"));

			$T=new Table();
			$T->Insert(1,1,"Launch information");
			$T->Insert(1,2,"Ships");
			$T->SetClass(1,2,'legend');
			$T->Insert(2,2,"Vipers");
			$T->Insert(3,2,"{$POST['vpr']}");
			$T->Insert(2,3,"Interceptors");
			$T->Insert(3,3,"{$POST['int']}");
			$T->Insert(2,4,"Frigates");
			$T->Insert(3,4,"{$POST['fr']}");
			$T->Insert(2,5,"Battleships");
			$T->Insert(3,5,"{$POST['bs']}");
			$T->Insert(2,6,"Dreadnoughts");
			$T->Insert(3,6,"{$POST['drn']}");
			$T->Insert(2,7,"Transporters");
			$T->Insert(3,7,"{$POST['tr']}");
			$T->Insert(2,8,"Colony Ships");
			$T->Insert(3,8,"{$POST['cs']}");
			$T->Join(1,2,1,7);

			$T->Insert(1,9,"Orders");
			$T->Insert(2,9,"Mission");
			$T->Insert(3,9,missionName($POST['mis']));
			$T->SetClass(1,9,'legend');


			$T->Insert(1,10,"From");
			$T->SetClass(1,10,'legend');
			$T->Insert(2,10,"Planet");
			$T->Insert(3,10,planet_get_name($sql, $POST['from']) . "");
			$T->Insert(2,11,"Owner");
			$plfrom=planet_get_nick($sql, $POST['from']);
			$T->Insert(3,11,"[{$plfrom['TAG']}] {$plfrom['Nick']}");
			$T->Join(1,10,1,2);


			$T->Insert(1,12,"To");
			$T->SetClass(1,12,'legend');
			$T->Insert(2,12,"Planet");
			$T->Insert(3,12,planet_get_name($sql, $to) . "");
			$T->Insert(2,13,"Owner");
			$plto=planet_get_nick($sql, $to);
			$T->Insert(3,13,"[{$plto['TAG']}] {$plto['Nick']}");
			$T->Join(1,12,1,2);

			$T->Insert(1,14,"Travel");
			$T->SetClass(1,14,'legend');
			$details=array();
			$ETA=fleet_simulate($sql,$MainPID,$POST['from'],$to,
					fleet_get_speed($POST['vpr'],$POST['int'],$POST['fr'],
						$POST['bs'],$POST['drn'],$POST['cs'],$POST['tr']),
					$details);

			$TTs=time_period($details['TT']);
			$T->Insert(2,14,"Travel Time");
			$T->Insert(3,14,$TTs . "");
			$T->Insert(2,15,"STL usage time");
			$STLprc=round($details['STL']*100/$details['TT']);
			$T->Insert(3,15,"$STLprc%");
			$T->Insert(2,16,"FTL usage time");
			$FTLprc=100-$STLprc;
			$T->Insert(3,16,"$FTLprc%");
			$T->Insert(2,17,"ETA");
			$T->Insert(3,17,DecodeTime($ETA));
			$T->Join(1,14,1,4);
			$T->Join(1,1,3,1);
			$T->SetClass(1,1,"title");
			for ($i=2; $i<=17; ++$i)
				$T->SetClass(2,$i,'sublegend');
			$T->sClass='block fleet';
			$H->Insert($T);

			if ($MainPID==$_SESSION['PID'])
				$F=new Form("fleet.php",true);
			else
				$F=new Form("fleet.php?sit=1",true);
			$POST['confirm']="1";
			unset($POST['send']);
			foreach ($POST as $Key => $Val)
			{
				if ($Key!="orderid")
					$F->Insert(new Input("hidden","$Key","$Val"));
				$F->Insert(new Input("hidden","orderid",$_SESSION['PostCode']+1));
			}
			if (!$sittingLaunchAbort)
				$F->Insert(new Input("submit","send","Launch!","smbutton"));
			$H->Insert($F);
		}
		elseif (!$sittingLaunchAbort)
		{
			if ($POST['mis']>0 && $POST['mis']<5) {
				$Answer=fleet_send($sql,$MainPID,$POST['vpr'],$POST['int'],$POST['fr'],$POST['bs'],$POST['drn'],$POST['cs'],$POST['tr'],$POST['from'],$to,99999,$POST['mis']);
				if ($Answer === true)
					$H->Insert(new Info("Ships succesfully launched"));
				elseif ($Answer === false)
					$H->Insert(new Error("Unknown internal error"));
				else
					$H->Insert(new Error($Answer));
			}
		}
	}
}

post("GateAddr","string");
if ($POST['send']=="Jump!" and $POST['GateAddr']!='')
{
	post("from","integer");
	if (planet_gateway_available($sql, $POST['from'], $MainPID))
	{
		post("vpr","integer");
		post("int","integer");
		post("fr","integer");
		post("bs","integer");
		post("drn","integer");
		post("tr","integer");
		post("cs","integer");
		$to=makeinteger(planet_gateway_find(&$sql, $POST['GateAddr']));
		if ($to==0)
			$to=$POST['from'];
		$Answer=fleet_send($sql,$MainPID,$POST['vpr'],$POST['int'],$POST['fr'],$POST['bs'],$POST['drn'],$POST['cs'],$POST['tr'],$POST['from'],-$to);
		if ($Answer === true)
			$H->Insert(new Info("Ships succesfully launched"));
		elseif ($Answer === false)
			$H->Insert(new Error("Unknown internal error"));
		else
			$H->Insert(new Error($Answer));
	}
}

$A=fleet_get_stationary($sql, $MainPID);
$B=fleet_get_intransit($sql, $MainPID);
if (count($A)+count($B)==0)
{
	$H->Insert(new Error("You have no fleet"));
	include("part/mainsubmenu.php");
	$H->Draw();
	die;
}

$T=new Table();

$T->SetCols(7);
$T->Insert(1,1,"Stationary fleets");
$T->Join(1,1,7,1);
$T->aRowClass[1]='title';

$T->Insert(1,2,"Location");
$T->Insert(2,2,"Vpr");
$T->Insert(3,2,"Int");
$T->Insert(4,2,"Fr");
$T->Insert(5,2,"Bs");
$T->Insert(6,2,"Drn");
$T->Insert(7,2,"CS");
$T->Insert(8,2,"Tr");
$T->aRowClass[2]='legend';
$T->SetClass(1,2,'location');


$T->sClass='block fleet';

$i=2;
foreach ($A as $F)
{
    ++$i;
    $T->Insert(1,$i,new Link("detail.php?id={$F['SID']}" . $sitAddition,"{$F['Name']} {$F['Ring']}"));
    $T->Insert(2,$i,"{$F['Vpr']}");
    $T->Insert(3,$i,"{$F['Int']}");
    $T->Insert(4,$i,"{$F['Fr']}");
    $T->Insert(5,$i,"{$F['Bs']}");
    $T->Insert(6,$i,"{$F['Drn']}");
    $T->Insert(7,$i,"{$F['CS']}");
    $T->Insert(8,$i,"{$F['Tr']}");
    $T->Insert(9,$i,new Link("launch.php?id={$F['PLID']}" . $sitAddition,"<b>Launch</b>"));
    if ($F['Siege']==1) $T->aRowClass[$i]='siegedplanet';
}

$H->Insert($T);

$T=new Table();

$T->SetCols(10);
$T->Insert(1,1,"Fleets in transit (" . fleet_slot_count($sql, $MainPID) . "/" . fleet_max_slot($sql, $MainPID) . ")");
$T->Join(1,1,10,1);
$T->aRowClass[1]='title';


$T->Insert(1,2,"Target");
$T->SetClass(1,2,'location');
$T->Insert(2,2,"Vpr");
$T->Insert(3,2,"Int");
$T->Insert(4,2,"Fr");
$T->Insert(5,2,"Bs");
$T->Insert(6,2,"Drn");
$T->Insert(7,2,"CS");
$T->Insert(8,2,"Tr");
$T->Insert(9,2,"Mission");
$T->Insert(10,2,"ETA");
$T->aRowClass[2]='legend';

$T->sClass='block fleet';

$i=2;
foreach ($B as $F)
{
    ++$i;
    $T->Insert(1,$i,"{$F['Name']} {$F['Ring']}");
    $T->Insert(2,$i,"{$F['Vpr']}");
    $T->Insert(3,$i,"{$F['Int']}");
    $T->Insert(4,$i,"{$F['Fr']}");
    $T->Insert(5,$i,"{$F['Bs']}");
    $T->Insert(6,$i,"{$F['Drn']}");
    $T->Insert(7,$i,"{$F['CS']}");
    $T->Insert(8,$i,"{$F['Tr']}");
		$T->Insert(9,$i,missionName($F['Mission']));
    $T->Insert(10,$i,DecodeTime($F['ETA']));
}

$H->Insert($T);
include("part/mainsubmenu.php");
$H->Draw();
CloseSQL($sql);
?>
