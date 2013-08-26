<?php

include_once("internal/html.php");
include_once("internal/common.php");
include_once("internal/security/validator.php");

include_once("internal/player.php");
include_once("internal/alliance.php");
include_once("internal/fleet.php");

session_start();

global $GET;
$GET=array();

$H = new HTML();
$H->sStyleThemePrefix="NC";
$H->AddStyle("default.css");
$H->AddStyle("falliance.css");

$H->sTitle="Northern Cross";

$sql=&OpenSQL($H);

$menuselected="Alliance";
include("part/mainmenu.php");

ForceActivePlayer($sql, $H, "empty.php");
ForceFrozen($sql, $H);
ForceNoSitting($sql, $H, $_SESSION['PID']);


$Tag=player_get_tag($sql, $_SESSION['PID']);
if ($Tag=="") {
    $H->Insert(new Error("You are not in an alliance yet"));
    CloseSQL($sql);
    $H->Draw();
    die;
}

$AllFleetMoves=alliance_get_fleet_moves($sql, $Tag);
$Inc=$AllFleetMoves[0];
$Mov=$AllFleetMoves[1];
//var_dump($Mov);

$IncSize=count($Inc);
$MovSize=count($Mov);
$IncPos=0;
$MovPos=0;

//$H->Insert(new Error("Warning: Incomings launched before 16 Oct 2007, 14:00GMT<br>have incorrect Transporters count, incorrect target planet and its defense"));
$T=new Table();
$T->Insert(1,1,"Fleet movements in alliance territory");
$T->Insert(1,2,"Fleet owner");
$T->Insert(1,3,"Tag");
$T->Insert(2,3,"Name");
$T->Join(1,2,2,1);
$T->Insert(3,2,"Flight target");
$T->Insert(3,3,"Mission");
$T->Insert(4,3,"ETA");
$T->Insert(5,3,"Location");
$T->Insert(6,3,"Current owner");
$T->Join(3,2,4,1);
$T->Insert(7,2,"Fleet size");
$T->Insert(7,3,"Vpr");
$T->Insert(8,3,"Int");
$T->Insert(9,3,"Fr");
$T->Insert(10,3,"Bs");
$T->Insert(11,3,"Drn");
$T->Insert(12,3,"TR");
$T->Join(7,2,6,1);
$T->Insert(13,2,"Known planet defense");
$T->Insert(13,3,"Vpr");
$T->Insert(14,3,"Int");
$T->Insert(15,3,"Fr");
$T->Insert(16,3,"Bs");
$T->Insert(17,3,"Drn");
$T->Insert(18,3,"SB");
$T->Join(13,2,6,1);
$T->Join(1,1,18,1);
$T->aRowClass[1]='title';
$T->aRowClass[2]='legend';
$T->aRowClass[3]='legend';

$row=3;
while ($IncPos<$IncSize or $MovPos<$MovSize)
{
    if ($IncPos==$IncSize)
	$UseInc=false;
    elseif ($MovPos==$MovSize)
	$UseInc=true;
    elseif ($Inc[$IncPos]['Time']<=$Mov[$MovPos]['ETA'])
	$UseInc=true;
    else
	$UseInc=false;

    ++$row;

    if ($UseInc)
	$classPrefix='i';
    else {
    	if ($Mov[$MovPos]['AttPID']!=$Mov[$MovPos]['DefPID']) {
		if ($Mov[$MovPos]['AttTAG']!=$Mov[$MovPos]['DefTAG'])
			$classPrefix='a';
		else
			$classPrefix='f';
		}
	else
		$classPrefix='m';
	}

		if ($UseInc) {

			$T->Insert(1,$row,'' . $Inc[$IncPos]['AttTAG']);
			$T->Insert(2,$row,'' . $Inc[$IncPos]['AttNick']);
			$T->Insert(3,$row,missionName($Inc[$IncPos]['Mission']));
			$T->Insert(4,$row,'' . DecodeTime($Inc[$IncPos]['Time']));
			$T->Insert(5,$row,$Inc[$IncPos]['Name'].' '.$Inc[$IncPos]['Ring']);
			if ($Inc[$IncPos]['DefTAG']!="")
				$T->Insert(6,$row,'['.$Inc[$IncPos]['DefTAG'].']');
			$T->Insert(6,$row,$Inc[$IncPos]['DefNick']);
			$T->Insert(7,$row,$Inc[$IncPos]['IncVpr']);
			$T->Insert(8,$row,$Inc[$IncPos]['IncInt']);
			$T->Insert(9,$row,$Inc[$IncPos]['IncFr']);
			$T->Insert(10,$row,$Inc[$IncPos]['IncBs']);
			$T->Insert(11,$row,$Inc[$IncPos]['IncDrn']);
			$T->Insert(12,$row,'' . $Inc[$IncPos]['IncTr']);
			if ($Inc[$IncPos]['DefTAG']==$Tag) {
				$T->Insert(13,$row,$Inc[$IncPos]['DefVpr']);
				$T->Insert(14,$row,$Inc[$IncPos]['DefInt']);
				$T->Insert(15,$row,$Inc[$IncPos]['DefFr']);
				$T->Insert(16,$row,$Inc[$IncPos]['DefBs']);
				$T->Insert(17,$row,$Inc[$IncPos]['DefDrn']);	
			}
			$T->Insert(18,$row,$Inc[$IncPos]['DefSb']);
			++$IncPos;
		}
		else {
			$T->Insert(1,$row,'' . $Mov[$MovPos]['AttTAG']);
			$T->Insert(2,$row,'' . $Mov[$MovPos]['AttNick']);
			$T->Insert(3,$row,missionName($Mov[$MovPos]['Mission']));
			$T->Insert(4,$row,'' . DecodeTime($Mov[$MovPos]['ETA']));
			$T->Insert(5,$row,$Mov[$MovPos]['Name'].' '.$Mov[$MovPos]['Ring']);
			if ($Mov[$MovPos]['DefTAG']!="")
				$T->Insert(6,$row,'['.$Mov[$MovPos]['DefTAG'].']');
			$T->Insert(6,$row,'' . $Mov[$MovPos]['DefNick']);
			$T->Insert(7,$row,$Mov[$MovPos]['Vpr']);
			$T->Insert(8,$row,$Mov[$MovPos]['Int']);
			$T->Insert(9,$row,$Mov[$MovPos]['Fr']);
			$T->Insert(10,$row,$Mov[$MovPos]['Bs']);
			$T->Insert(11,$row,$Mov[$MovPos]['Drn']);
			$T->Insert(12,$row,$Mov[$MovPos]['Tr']);
			if ($Mov[$MovPos]['DefTAG']==$Tag) {
				$T->Insert(13,$row,$Mov[$MovPos]['DefVpr']);
				$T->Insert(14,$row,$Mov[$MovPos]['DefInt']);
				$T->Insert(15,$row,$Mov[$MovPos]['DefFr']);
				$T->Insert(16,$row,$Mov[$MovPos]['DefBs']);
				$T->Insert(17,$row,$Mov[$MovPos]['DefDrn']);	
			}
			$T->Insert(18,$row,$Mov[$MovPos]['DefSb']);	
			++$MovPos;
		}

    for ($col=1; $col<=6; ++$col)
	$T->SetClass($col,$row,$classPrefix);
    for ($col=7; $col<=12; ++$col)
	$T->SetClass($col,$row,$classPrefix . 'a');
    for ($col=13; $col<=18; ++$col)
	$T->SetClass($col,$row,$classPrefix . 'd');
    
}

$T->sClass='block';
$H->Insert($T);
$H->Draw();
CloseSQL($sql);
?>
